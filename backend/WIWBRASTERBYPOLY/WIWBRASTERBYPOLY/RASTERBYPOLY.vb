Option Explicit On

Imports METEOBAS
Imports System

Module RASTERBYPOLY

    'Copyright Siebe Bosch Hydroconsult, 2012
    'Lulofsstraat 55, unit 47 Den Haag, The Netherlands
    'this program converts precipitation and/or evaporation data from Meteobase (NetCDF)
    'to a .CSV-file, given an uploaded shapefile.

    'lokale variabelen
    Dim Setup As New METEOBAS.General.clsSetup
    Dim RasterData As New METEOBAS.clsWIWBRasterData(Setup)

    Sub Main()
        '####################################################################
        'debugsettings. Allows switching between running on server or locally.
        'debugmode uses some default settings when running
        'runonserver changes the source directories 
        '####################################################################

        Dim myArg As String
        Console.WriteLine("This program extracts data from the WIWB API and aggregates raster data by polygon.")

        '----------------------------------------------------------------------------------------------------------------------------
        'system-dependent variables
        '----------------------------------------------------------------------------------------------------------------------------
        If Debugger.IsAttached Then
            RasterData.RasterDir = "d:\Dropbox\MeteoBase\RasterData"                                                                      'laptop en server
            RasterData.TempDir = "c:\Result"
            RasterData.GDALToolsDir = "c:\GDAL"                                                                                   'laptop
            RasterData.DownloadURL = "https://www.meteobase.nl/meteobase/downloads/"
            RasterData.DownloadDIR = "c:\temp\"                                                                                   'laptop
        Else
            RasterData.RasterDir = "D:\METEOBASE\RasterData"                                                                     'server
            RasterData.TempDir = "c:\Result"
            RasterData.GDALToolsDir = "c:\Program Files\GDAL\"                                                                             'server
            RasterData.DownloadURL = "https://www.meteobase.nl/meteobase/downloads/"
            RasterData.DownloadDIR = "c:\Program Files (x86)\PostgreSQL\EnterpriseDB-ApachePHP\apache\www\meteobase\downloads\"  'server
        End If

        '----------------------------------------------------------------------------------------------------------------------------
        'reading the user's arguments
        '----------------------------------------------------------------------------------------------------------------------------
        If Debugger.IsAttached Then
            RasterData.FDate = 20180101
            RasterData.TDate = 20180301
            RasterData.NSL = True
            RasterData.MAKKINK = False
            RasterData.PM = False
            RasterData.EVT_ACTUAL = False
            RasterData.EVT_SHORTAGE = False
            RasterData.FORMAAT = "SOBEK"  'switch etween CSV and SOBEK
            RasterData.SessionID = 11111
            RasterData.OrderNum = 11111
            RasterData.Naam = "Siebe Bosch"
            RasterData.MailAdres = "siebe@watercommunicatie.nl"
            RasterData.ShapeFileZIP = "c:\SYNC\SOFTWARE\METEOBASE\Support\20231120_Noortje\gebieden_meteo_stations.zip"
            'RasterData.ShapeFileZIP = "c:\Dropbox\MeteoBase\DEMODATA\SPIJKSTERPOMPEN_LAYER.zip"
            'RasterData.ShapeFileZIP = "d:\Dropbox\MeteoBase\Exchange\neerslagall.zip"
            'RasterData.ShapeFileZIP = "d:\Dropbox\MeteoBase\DEMODATA\VOORNEOOST_GAFIDENT.ZIP"
            'RasterData.ShapeFileZIP = "d:\Dropbox\MeteoBase\DEMODATA\SMILDE_GFEIDENT.ZIP"
            'RasterData.ShapeFileZIP = "d:\Dropbox\MeteoBase\DEMODATA\FRYSLAN_WATERSYSTE.ZIP"
            'RasterData.ShapeFileZIP = "D:\MeteoBase\DemoData\ARCADIS\afwateringsgebied.zip"
            'RasterData.ShapeFileZIP = "D:\Meteobase\Demodata\HKV\Fishnet.zip"
            RasterData.ShapeField = "MS_id"
            'RasterData.ShapeField = "WATERSYSTE"    'for FRYSLAN
            'RasterData.ShapeField = "layer"    'for Spijksterpompen
            'RasterData.ShapeField = "subcatchme"    'for IMBER
            'RasterData.ShapeField = "GFEIDENT"    'for SMILDE
            'RasterData.ShapeField = "GPGIDENT"    'for HHNK
            'RasterData.ShapeField = "AREA"        'for fisnhet.zip
        Else
            If My.Application.CommandLineArgs.Count = 0 Then
                Console.WriteLine("Enter start date (YYYYMMDD)")
                myArg = Console.ReadLine
                RasterData.FDate = myArg
                Setup.Log.CmdArgs.Add(myArg)
                Console.WriteLine("Enter end date (YYYYMMDD)")
                myArg = Console.ReadLine
                RasterData.TDate = myArg
                Setup.Log.CmdArgs.Add(myArg)
                Console.WriteLine("Export precipitation? (TRUE/FALSE)")
                myArg = Console.ReadLine
                RasterData.NSL = myArg
                Setup.Log.CmdArgs.Add(myArg)
                Console.WriteLine("Export Makkink evaporation? (TRUE/FALSE)")
                myArg = Console.ReadLine
                RasterData.MAKKINK = myArg
                Setup.Log.CmdArgs.Add(myArg)
                Console.WriteLine("Export Penman evaporation? (TRUE/FALSE)")
                myArg = Console.ReadLine
                RasterData.PM = myArg
                Setup.Log.CmdArgs.Add(myArg)
                Console.WriteLine("Export SATDATA 3.0 actual evapotranspiration? (TRUE/FALSE)")
                myArg = Console.ReadLine
                RasterData.EVT_ACTUAL = myArg
                Setup.Log.CmdArgs.Add(myArg)
                Console.WriteLine("Export SATDATA 3.0 evapotranspiration shortage? (TRUE/FALSE)")
                myArg = Console.ReadLine
                RasterData.EVT_SHORTAGE = myArg
                Setup.Log.CmdArgs.Add(myArg)
                Console.WriteLine("Which format? (CSV/SOBEK)")
                myArg = Console.ReadLine
                RasterData.FORMAAT = myArg.Trim.ToUpper
                Setup.Log.CmdArgs.Add(myArg)
                Console.WriteLine("Enter the session ID")
                myArg = Console.ReadLine
                RasterData.SessionID = myArg
                Setup.Log.CmdArgs.Add(myArg)
                Console.WriteLine("Enter the order number")
                myArg = Console.ReadLine
                RasterData.OrderNum = myArg
                Setup.Log.CmdArgs.Add(myArg)
                Console.WriteLine("Enter the name of the person who orders")
                myArg = Console.ReadLine
                RasterData.Naam = myArg
                Setup.Log.CmdArgs.Add(myArg)
                Console.WriteLine("Enter their e-mailaddress")
                myArg = Console.ReadLine
                RasterData.MailAdres = myArg
                Setup.Log.CmdArgs.Add(myArg)
                Console.WriteLine("Enter the path to the shapefile")
                myArg = Console.ReadLine
                RasterData.ShapeFileZIP = myArg
                Setup.Log.CmdArgs.Add(myArg)
                Console.WriteLine("Enter the name of the ShapeField")
                myArg = Console.ReadLine
                RasterData.ShapeField = myArg
                Setup.Log.CmdArgs.Add(myArg)
            ElseIf My.Application.CommandLineArgs.Count <> 14 Then
                Console.WriteLine("Error: incorrect number of arguments presented")
            Else
                RasterData.FDate = Convert.ToInt32(My.Application.CommandLineArgs(0))
                RasterData.TDate = Convert.ToInt32(My.Application.CommandLineArgs(1))
                RasterData.NSL = Setup.GeneralFunctions.GetBooleanFromString(My.Application.CommandLineArgs(2))
                RasterData.MAKKINK = Setup.GeneralFunctions.GetBooleanFromString(My.Application.CommandLineArgs(3))
                RasterData.PM = Setup.GeneralFunctions.GetBooleanFromString(My.Application.CommandLineArgs(4))
                RasterData.EVT_ACTUAL = Setup.GeneralFunctions.GetBooleanFromString(My.Application.CommandLineArgs(5))
                RasterData.EVT_SHORTAGE = Setup.GeneralFunctions.GetBooleanFromString(My.Application.CommandLineArgs(6))
                RasterData.FORMAAT = My.Application.CommandLineArgs(7).Trim.ToUpper
                RasterData.SessionID = Convert.ToInt32(My.Application.CommandLineArgs(8))
                RasterData.OrderNum = Convert.ToInt32(My.Application.CommandLineArgs(9))
                RasterData.Naam = Setup.GeneralFunctions.RemoveBoundingQuotes(My.Application.CommandLineArgs(10))
                RasterData.MailAdres = Setup.GeneralFunctions.RemoveBoundingQuotes(My.Application.CommandLineArgs(11))
                RasterData.ShapeFileZIP = Setup.GeneralFunctions.RemoveBoundingQuotes(My.Application.CommandLineArgs(12))
                RasterData.ShapeField = Setup.GeneralFunctions.RemoveBoundingQuotes(My.Application.CommandLineArgs(13))
                For i = 0 To 13
                    Setup.Log.CmdArgs.Add(My.Application.CommandLineArgs(i))
                Next
            End If
        End If

        '----------------------------------------------------------------------------------------------------------------------------
        'setting values for derived variables
        '----------------------------------------------------------------------------------------------------------------------------
        RasterData.ZipFile = RasterData.SessionID.ToString.Trim & "_" & RasterData.OrderNum.ToString.Trim & ".zip"
        RasterData.TempDir = RasterData.TempDir & "\" & Str(RasterData.OrderNum).Trim
        RasterData.Unzipdir = RasterData.TempDir & "\upload"
        If Not System.IO.Directory.Exists(RasterData.TempDir) Then System.IO.Directory.CreateDirectory(RasterData.TempDir)
        If Not System.IO.Directory.Exists(RasterData.Unzipdir) Then System.IO.Directory.CreateDirectory(RasterData.Unzipdir)

        '----------------------------------------------------------------------------------------------------------------------------
        'unzip the shapefile and start writing the SOBEK-data
        '----------------------------------------------------------------------------------------------------------------------------
        Dim body As String
        Try
            If Not RasterData.UnZipShapeFile Then Throw New Exception("Fout: zipbestand met shapefile kon niet worden uitgepakt.")

            If RasterData.Write() Then  'write the requested datafiles
                If RasterData.WriteZIP(RasterData.DownloadDIR & RasterData.ZipFile) Then
                    body = RasterData.GenerateGoodMailBody()
                    RasterData.GoodMail = New clsEmail(Setup)
                    Call RasterData.sendGoodEmail("Meteobase bestelling " & RasterData.OrderNum & " " & "Rasterdata geaggregeerd naar polygonen.", body)
                Else
                    Throw New Exception("Error writing zip-file containing results.")
                End If 'compress all files in FileCollection into one zipfile
            Else
                Throw New Exception("Error writing rasterdata.")
            End If
        Catch ex As Exception
            Setup.Log.AddError(ex.Message)
            body = RasterData.GenerateBadMailBody()
            RasterData.BadMail = New clsEmail(Setup)
            Call RasterData.sendBadEmail("Meteobase bestelling " & RasterData.OrderNum & " " & "Rasterdata", body)
        Finally
            'write the logfile to the download directory on the server for debugging purposes
            Setup.Log.write(RasterData.DownloadDIR & RasterData.ZipFile & ".log", False)
        End Try

    End Sub

    Private Function ExportCommandLineArgs(ByVal myPath As String) As Boolean
        Dim myStr As String = ""
        Using ArgWriter As New System.IO.StreamWriter(myPath)
            For Each myArg As String In My.Application.CommandLineArgs
                myStr &= " " & myArg
            Next
            ArgWriter.WriteLine(myStr)
        End Using
        Return True
    End Function



End Module

