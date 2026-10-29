<?php
// Checks for an uploaded, zipped shapefile (rasterdata per polygon, tool RASTERBYPOLY).
// Used when the zip is uploaded (pages/rasterDataUpload.php) and again when it is
// ordered (pages/rasterdata.php). Only the headers of .shp/.shx/.prj and the .dbf are
// read from the zip; nothing is extracted to disk. Messages are short Dutch texts for
// the user and contain no user input except field names from the .dbf (see
// SHAPE_FIELD_NAME), so they are safe in HTML and in a JS string.

const SHAPE_UPLOAD_DIR = 'C:/Apache24/htdocs/meteobase/uploads/';
const SHAPE_MAX_POLYGONS = 5000;
// RD New (EPSG:28992) extent of the Netherlands
const SHAPE_RD_XMIN = 0;
const SHAPE_RD_XMAX = 300000;
const SHAPE_RD_YMIN = 289000;
const SHAPE_RD_YMAX = 629000;
// field types in the .dbf that can hold an area ID: text, numeric, float
const SHAPE_ID_FIELD_TYPES = ['C', 'N', 'F'];
// field names that are offered and accepted (a .dbf name is at most 11 characters)
const SHAPE_FIELD_NAME = '/^[A-Za-z0-9_]{1,11}$/';

const SHAPE_MSG_RD = 'De shapefile ligt niet in RD (EPSG:28992) of valt buiten Nederland.';

class ShapeCheckError extends Exception {}

// Checks the zip and returns ['count' => number of polygons, 'fields' => usable field names].
// Throws ShapeCheckError with a message for the user.
function shape_inspect_zip(string $zipPath): array {
    $zip = shape_open_zip($zipPath);
    try {
        $parts = shape_find_parts($zip);

        // .shp header: file code 9994 (big-endian), shape type and bounding box (little-endian)
        $h = shape_read($zip, $parts['shp'], 100);
        if (strlen($h) < 100 || unpack('N', substr($h, 0, 4))[1] !== 9994) {
            throw new ShapeCheckError('Het .shp-bestand is geen geldige shapefile.');
        }
        $type = unpack('V', substr($h, 32, 4))[1];
        if (!in_array($type, [5, 15, 25], true)) {
            throw new ShapeCheckError('Alleen polygonen worden ondersteund.');
        }
        $box = unpack('exmin/eymin/exmax/eymax', substr($h, 36, 32));
        foreach ($box as $v) {
            if (!is_finite($v)) {
                throw new ShapeCheckError(SHAPE_MSG_RD);
            }
        }
        if ($box['xmin'] < SHAPE_RD_XMIN || $box['xmax'] > SHAPE_RD_XMAX
            || $box['ymin'] < SHAPE_RD_YMIN || $box['ymax'] > SHAPE_RD_YMAX) {
            throw new ShapeCheckError(SHAPE_MSG_RD);
        }

        // a .prj is optional (the tool assumes RD), but if there is one it must be RD
        if ($parts['prj'] !== null) {
            $prj = shape_read($zip, $parts['prj'], 8192);
            if (!preg_match('/Amersfoort|RD[_ ]?New|28992/i', $prj)) {
                throw new ShapeCheckError(SHAPE_MSG_RD);
            }
        }

        // number of shapes from the size of the .shx (100-byte header + 8 bytes per shape)
        $shx = $zip->statName($parts['shx']);
        if ($shx === false || $shx['size'] < 100 || ($shx['size'] - 100) % 8 !== 0) {
            throw new ShapeCheckError('Het .shx-bestand is beschadigd.');
        }
        $count = intdiv($shx['size'] - 100, 8);

        $dbf = shape_dbf_header($zip, $parts['dbf']);
        if ($dbf['records'] !== $count) {
            throw new ShapeCheckError('De .shp, .shx en .dbf horen niet bij elkaar (verschillend aantal objecten).');
        }
        if ($count === 0) {
            throw new ShapeCheckError('De shapefile bevat geen polygonen.');
        }
        if ($count > SHAPE_MAX_POLYGONS) {
            throw new ShapeCheckError('De shapefile bevat ' . $count . ' polygonen; maximaal ' . SHAPE_MAX_POLYGONS . ' zijn toegestaan.');
        }
        $fields = [];
        foreach ($dbf['fields'] as $f) {
            if (in_array($f['type'], SHAPE_ID_FIELD_TYPES, true) && preg_match(SHAPE_FIELD_NAME, $f['name'])) {
                $fields[] = $f['name'];
            }
        }
        if (!$fields) {
            throw new ShapeCheckError('De .dbf bevat geen tekst- of getalveld dat als gebieds-ID kan dienen.');
        }
        return ['count' => $count, 'fields' => $fields];
    } finally {
        $zip->close();
    }
}

// True if every polygon has a value in $field and no value occurs twice. Values are
// compared like the tool does (trimmed, case-insensitive). Deleted records are skipped.
function shape_field_values_ok(string $zipPath, string $field): bool {
    $zip = shape_open_zip($zipPath);
    try {
        $parts = shape_find_parts($zip);
        $dbf = shape_dbf_header($zip, $parts['dbf']);
        $col = null;
        foreach ($dbf['fields'] as $f) {
            if (strcasecmp($f['name'], $field) === 0) {
                $col = $f;
            }
        }
        if ($col === null) {
            return false;
        }
        $stream = $zip->getStream($parts['dbf']);
        if ($stream === false) {
            return false;
        }
        try {
            if (shape_fread($stream, $dbf['header']) === null) {
                return false;
            }
            $seen = [];
            for ($i = 0; $i < $dbf['records']; $i++) {
                $rec = shape_fread($stream, $dbf['record']);
                if ($rec === null) {
                    return false;
                }
                if ($rec[0] === '*') {
                    continue;
                }
                $value = trim(substr($rec, $col['offset'], $col['length']), " \0");
                if ($value === '') {
                    return false;
                }
                $key = $col['type'] === 'C' ? strtoupper($value) : (string)(float)$value;
                if (isset($seen[$key])) {
                    return false;
                }
                $seen[$key] = true;
            }
            return true;
        } finally {
            fclose($stream);
        }
    } catch (ShapeCheckError $e) {
        return false;
    } finally {
        $zip->close();
    }
}

// ---- helpers ------------------------------------------------------------------

function shape_open_zip(string $zipPath): ZipArchive {
    $zip = new ZipArchive();
    if (!is_file($zipPath) || $zip->open($zipPath, ZipArchive::RDONLY) !== true) {
        throw new ShapeCheckError('Het bestand is geen geldig zip-bestand.');
    }
    return $zip;
}

// Finds the one shapefile in the zip (folders allowed) and returns the entry names of
// its .shp, .shx, .dbf and optional .prj (same base name, case-insensitive).
function shape_find_parts(ZipArchive $zip): array {
    $entries = [];
    $shp = [];
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $name = $zip->getNameIndex($i);
        if ($name === false) {
            throw new ShapeCheckError('Het bestand is geen geldig zip-bestand.');
        }
        $path = str_replace('\\', '/', $name);
        if ($path === '' || $path[0] === '/' || preg_match('#^[A-Za-z]:#', $path)
            || in_array('..', explode('/', $path), true)) {
            throw new ShapeCheckError('Het zip-bestand bevat ongeldige bestandsnamen (absolute paden of ..).');
        }
        // folders and macOS metadata (__MACOSX/, ._name.shp) are ignored
        if (substr($path, -1) === '/' || strpos($path, '__MACOSX/') === 0 || strpos(basename($path), '._') === 0) {
            continue;
        }
        $entries[strtolower($path)] = $name;
        if (strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'shp') {
            $shp[] = strtolower($path);
        }
    }
    if (count($shp) === 0) {
        throw new ShapeCheckError('Het zip-bestand bevat geen shapefile (.shp).');
    }
    if (count($shp) > 1) {
        throw new ShapeCheckError('Het zip-bestand bevat meer dan één shapefile (.shp); upload er één tegelijk.');
    }
    $stem = substr($shp[0], 0, -4);
    $missing = [];
    foreach (['shx', 'dbf'] as $ext) {
        if (!isset($entries["$stem.$ext"])) {
            $missing[] = '.' . $ext;
        }
    }
    if ($missing) {
        throw new ShapeCheckError('De shapefile is niet compleet: ' . implode(' en ', $missing)
            . ' ontbreekt. Een shapefile bestaat uit .shp, .shx en .dbf met dezelfde naam.');
    }
    return [
        'shp' => $entries["$stem.shp"],
        'shx' => $entries["$stem.shx"],
        'dbf' => $entries["$stem.dbf"],
        'prj' => $entries["$stem.prj"] ?? null,
    ];
}

// The first $length bytes of an entry (less if the entry is shorter).
function shape_read(ZipArchive $zip, string $entry, int $length): string {
    $stream = $zip->getStream($entry);
    if ($stream === false) {
        throw new ShapeCheckError('Het zip-bestand kon niet worden gelezen.');
    }
    $data = '';
    while (strlen($data) < $length && !feof($stream)) {
        $chunk = fread($stream, $length - strlen($data));
        if ($chunk === false || $chunk === '') {
            break;
        }
        $data .= $chunk;
    }
    fclose($stream);
    return $data;
}

// Exactly $length bytes from a stream, or null at the end of the data.
function shape_fread($stream, int $length): ?string {
    $data = '';
    while (strlen($data) < $length) {
        $chunk = fread($stream, $length - strlen($data));
        if ($chunk === false || $chunk === '') {
            return null;
        }
        $data .= $chunk;
    }
    return $data;
}

// Record count, header and record length, and the fields (name, type, offset, length) of a .dbf.
function shape_dbf_header(ZipArchive $zip, string $entry): array {
    $bad = 'Het .dbf-bestand is geen geldige dBase-tabel.';
    $h = shape_read($zip, $entry, 32);
    if (strlen($h) < 32) {
        throw new ShapeCheckError($bad);
    }
    $records = unpack('V', substr($h, 4, 4))[1];
    $headerLength = unpack('v', substr($h, 8, 2))[1];
    $recordLength = unpack('v', substr($h, 10, 2))[1];
    if ($headerLength < 33 || $recordLength < 1) {
        throw new ShapeCheckError($bad);
    }
    $h = shape_read($zip, $entry, $headerLength);
    if (strlen($h) < $headerLength) {
        throw new ShapeCheckError($bad);
    }
    $fields = [];
    $offset = 1; // byte 0 of a record is the deletion flag
    for ($pos = 32; $pos + 32 <= $headerLength && $h[$pos] !== "\x0D"; $pos += 32) {
        $d = substr($h, $pos, 32);
        $length = ord($d[16]);
        $fields[] = [
            'name' => explode("\0", substr($d, 0, 11))[0],
            'type' => strtoupper($d[11]),
            'offset' => $offset,
            'length' => $length,
        ];
        $offset += $length;
    }
    if ($offset > $recordLength) {
        throw new ShapeCheckError($bad);
    }
    return ['records' => $records, 'header' => $headerLength, 'record' => $recordLength, 'fields' => $fields];
}
