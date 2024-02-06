
Option Explicit On

Imports METEOBAS
Imports System
Imports Newtonsoft.Json.Linq
Imports System.Reflection

Module NATIVERASTER

    'Copyright Siebe Bosch Hydroconsult, 2017
    'Lulofsstraat 55, unit 47 Den Haag, The Netherlands
    'this program extracts precipitation and/or evaporation data from the WIWB API
    'in its own native format (currently HDF5)

    'lokale variabelen
    Dim Setup As New METEOBAS.General.clsSetup
    Dim RasterData As New METEOBAS.clsWIWBRasterData(Setup)

    Sub Main()
        Console.WriteLine("This program converts rasterdata from the WIWB API to ASCII grids for various simulation models")

        ' Write the version number to the console
        Dim version As Version = Assembly.GetExecutingAssembly().GetName().Version
        Console.WriteLine("Application Version: " & version.ToString())

        '----------------------------------------------------------------------------------------------------------------------------
        'system-dependent variables
        '----------------------------------------------------------------------------------------------------------------------------
        If Debugger.IsAttached Then
            RasterData.GDALToolsDir = "c:\Program Files\GDAL\"                                                                                'server
            RasterData.RasterDir = "D:\METEOBASE\RasterData"                                                                     'laptop en server
            RasterData.TempDir = "c:\temp"
            RasterData.DownloadURL = "https://www.meteobase.nl/meteobase/downloads/"
            RasterData.DownloadDIR = "c:\Result\"                                                                                   'laptop
        Else
            RasterData.GDALToolsDir = "c:\Program Files\GDAL\"                                                                    'server
            RasterData.RasterDir = "D:\METEOBASE\RasterData"                                                                      'server
            RasterData.TempDir = "c:\Result"
            RasterData.DownloadURL = "https://www.meteobase.nl/meteobase/downloads/"
            'RasterData.DownloadDIR = "c:\Program Files (x86)\PostgreSQL\EnterpriseDB-Apache\Php\apache\www\meteobase\downloads\" 'server
            RasterData.DownloadDIR = "c:\Apache24\htdocs\meteobase\downloads\"
        End If


        '----------------------------------------------------------------------------------------------------------------------------
        'reading the user's arguments
        '----------------------------------------------------------------------------------------------------------------------------
        RasterData.FORMAAT = "HDF5"
        If My.Application.CommandLineArgs.Count = 0 Then
            Console.WriteLine("Enter start date (YYYYMMDD)")
            RasterData.FDate = Console.ReadLine
            Console.WriteLine("Enter end date (YYYYMMDD)")
            RasterData.TDate = Console.ReadLine
            Console.WriteLine("Enter Xmin (RD)")
            RasterData.Xmin = Console.ReadLine
            Console.WriteLine("Enter Ymin (RD)")
            RasterData.Ymin = Console.ReadLine
            Console.WriteLine("Enter Xmax (RD)")
            RasterData.Xmax = Console.ReadLine
            Console.WriteLine("Enter Ymax (RD)")
            RasterData.Ymax = Console.ReadLine
            Console.WriteLine("Export precipitation? (TRUE/FALSE)")
            RasterData.NSL = Setup.GeneralFunctions.BooleanFromText(Console.ReadLine)
            Console.WriteLine("Export Makkink evaporation? (TRUE/FALSE)")
            RasterData.MAKKINK = Setup.GeneralFunctions.BooleanFromText(Console.ReadLine)
            Console.WriteLine("Export Penman evaporation? (TRUE/FALSE)")
            RasterData.PM = Setup.GeneralFunctions.BooleanFromText(Console.ReadLine)
            Console.WriteLine("Export actual evapotranspiration? (TRUE/FALSE)")
            RasterData.EVT_ACTUAL = Setup.GeneralFunctions.BooleanFromText(Console.ReadLine)
            Console.WriteLine("Export evaporation shortage? (TRUE/FALSE)")
            RasterData.EVT_SHORTAGE = Setup.GeneralFunctions.BooleanFromText(Console.ReadLine)
            'Console.WriteLine("Aggregate to daily values? (TRUE/FALSE)")
            'RasterData.Aggregate24H = Setup.GeneralFunctions.BooleanFromText(Console.ReadLine)
            Console.WriteLine("Enter the session ID")
            RasterData.SessionID = Console.ReadLine
            Console.WriteLine("Enter the order number")
            RasterData.OrderNum = Console.ReadLine
            Console.WriteLine("Enter the name of the person who orders")
            RasterData.Naam = Console.ReadLine
            Console.WriteLine("Enter their e-mailaddress")
            RasterData.MailAdres = Console.ReadLine
        ElseIf My.Application.CommandLineArgs.Count <> 16 Then
            Console.WriteLine("Error: incorrect number of arguments presented")

            'store the command line arguments in the logfile
            For i = 0 To My.Application.CommandLineArgs.Count - 1
                Setup.Log.CmdArgs.Add(My.Application.CommandLineArgs(i))
            Next

        Else
            RasterData.FDate = My.Application.CommandLineArgs(0)
            RasterData.TDate = My.Application.CommandLineArgs(1)
            RasterData.Xmin = My.Application.CommandLineArgs(2)
            RasterData.Ymin = My.Application.CommandLineArgs(3)
            RasterData.Xmax = My.Application.CommandLineArgs(4)
            RasterData.Ymax = My.Application.CommandLineArgs(5)
            RasterData.NSL = Setup.GeneralFunctions.GetBooleanFromString(My.Application.CommandLineArgs(6))
            RasterData.MAKKINK = Setup.GeneralFunctions.GetBooleanFromString(My.Application.CommandLineArgs(7))
            RasterData.PM = Setup.GeneralFunctions.GetBooleanFromString(My.Application.CommandLineArgs(8))
            RasterData.EVT_ACTUAL = Setup.GeneralFunctions.GetBooleanFromString(My.Application.CommandLineArgs(9))
            RasterData.EVT_SHORTAGE = Setup.GeneralFunctions.GetBooleanFromString(My.Application.CommandLineArgs(10))
            'RasterData.Aggregate24H = Setup.GeneralFunctions.GetBooleanFromString(My.Application.CommandLineArgs(11))
            RasterData.SessionID = My.Application.CommandLineArgs(12)
            RasterData.OrderNum = My.Application.CommandLineArgs(13)
            RasterData.Naam = My.Application.CommandLineArgs(14)
            RasterData.MailAdres = My.Application.CommandLineArgs(15)

            'store the arguments for future reference (e.g. in diagnostics)
            For i = 0 To My.Application.CommandLineArgs.Count - 1
                Setup.Log.CmdArgs.Add(My.Application.CommandLineArgs(i))
            Next

        End If

        '----------------------------------------------------------------------------------------------------------------------------
        'setting values for derived variables
        '----------------------------------------------------------------------------------------------------------------------------
        RasterData.ZipFile = RasterData.SessionID.ToString.Trim & "_" & RasterData.OrderNum.ToString.Trim & "_" & RasterData.FORMAAT & ".zip"
        RasterData.TempDir = RasterData.TempDir & "\" & Str(RasterData.OrderNum).Trim
        If Not System.IO.Directory.Exists(RasterData.TempDir) Then System.IO.Directory.CreateDirectory(RasterData.TempDir)
        If Not System.IO.Directory.Exists(RasterData.DownloadDIR) Then System.IO.Directory.CreateDirectory(RasterData.DownloadDIR)

        '----------------------------------------------------------------------------------------------------------------------------
        'Start writing the results
        '----------------------------------------------------------------------------------------------------------------------------
        Dim body As String
        Try
            If RasterData.Write() Then  'write the requested datafiles
                If RasterData.WriteZIP(RasterData.DownloadDIR & RasterData.ZipFile) Then
                    'compress all files in FileCollection into one zipfile
                    body = RasterData.GenerateGoodMailBody()
                    RasterData.GoodMail = New clsEmail(Setup)
                    Call RasterData.sendGoodEmail("Meteobase bestelling " & RasterData.OrderNum & " " & "Rasterdata", body)
                Else
                    Throw New Exception("")
                End If
            Else
                Throw New Exception("")
            End If
        Catch ex As Exception
            Setup.Log.AddError(ex.Message)
            body = RasterData.GenerateBadMailBody()
            RasterData.BadMail = New clsEmail(Setup)
            Call RasterData.sendBadEmail("Meteobase bestelling " & RasterData.OrderNum & " " & "Rasterdata", body)
        Finally
            'cleaning up after ourselves
            If Not Debugger.IsAttached Then
                System.IO.Directory.Delete(RasterData.TempDir, True)
            End If
        End Try

    End Sub

End Module


