Option Explicit On

Imports METEOBAS
Imports System
Imports Newtonsoft.Json.Linq

Module HERHALINGSTIJD

    'Copyright Siebe Bosch Hydroconsult, 2012
    'Lulofsstraat 55, unit 47 Den Haag, The Netherlands
    'this program extracts precipitation and/or evaporation data from the WIWB API

    'lokale variabelen
    Dim Setup As New METEOBAS.General.clsSetup
    Dim HERH As New METEOBAS.clsWIWBHerhalingstijden(Setup)
    Dim GDALDemPath As String

    Sub Main()
        Dim body As String
        Try
            Dim myArg As String
            Console.WriteLine("This program reads precipitation and evaporation data from the WIWB API")

            '----------------------------------------------------------------------------------------------------------------------------
            'system-dependent variables
            '----------------------------------------------------------------------------------------------------------------------------
            If Debugger.IsAttached Then
                HERH.RasterViewURL = "https://www.meteobase.nl/images/rasterviews/"                                               'laptop en server
                HERH.RasterViewDIR = "c:\RasterView\"  'local
                HERH.DownloadURL = "https://www.meteobase.nl/meteobase/downloads/"                                                 'laptop en server
                HERH.DownloadDIR = "c:\result\"  'local
                HERH.TempDir = "c:\temp\"
                GDALDemPath = "c:\Program Files\GDAL\gdaldem.exe"
            Else
                HERH.RasterViewURL = "https://www.meteobase.nl/images/rasterviews/"                                               'laptop en server
                HERH.RasterViewDIR = "c:\Program Files (x86)\PostgreSQL\EnterpriseDB-ApachePHP\apache\www\images\rasterviews\"  'server
                HERH.DownloadURL = "https://www.meteobase.nl/meteobase/downloads/"                                                 'laptop en server
                HERH.DownloadDIR = "c:\Program Files (x86)\PostgreSQL\EnterpriseDB-ApachePHP\apache\www\meteobase\downloads\"  'server
                HERH.TempDir = "c:\temp\"
                GDALDemPath = "c:\Program Files\GDAL\gdaldem.exe"
            End If

            If Not System.IO.Directory.Exists(HERH.DownloadDIR) Then
                Throw New Exception("Error: directory does not exist: " & HERH.DownloadDIR)
            ElseIf Not System.IO.Directory.Exists(HERH.RasterViewDIR) Then
                Throw New Exception("Error: directory does not exist: " & HERH.RasterViewDIR)
            End If

            If My.Application.CommandLineArgs.Count = 0 Then
                Console.WriteLine("Enter start date (YYYYMMDD)")
                myArg = Console.ReadLine
                HERH.FDate = myArg
                Setup.Log.CmdArgs.Add(myArg)
                Console.WriteLine("Enter end date (YYYYMMDD)")
                myArg = Console.ReadLine
                HERH.TDate = myArg
                Setup.Log.CmdArgs.Add(myArg)
                Console.WriteLine("Enter your name")
                myArg = Console.ReadLine
                HERH.Naam = myArg
                Setup.Log.CmdArgs.Add(myArg)
                Console.WriteLine("Enter your email address")
                myArg = Console.ReadLine
                HERH.MailAdres = myArg
                Setup.Log.CmdArgs.Add(myArg)
                Console.WriteLine("Enter resultsfile path (png)")
                myArg = Console.ReadLine
                HERH.FileName = myArg
                Setup.Log.CmdArgs.Add(myArg)

            ElseIf My.Application.CommandLineArgs.Count <> 5 Then
                Console.WriteLine("Error: incorrect number of arguments presented")
            Else
                HERH.FDate = My.Application.CommandLineArgs(0)
                HERH.TDate = My.Application.CommandLineArgs(1)
                HERH.Naam = My.Application.CommandLineArgs(2)
                HERH.MailAdres = My.Application.CommandLineArgs(3)
                HERH.FileName = Setup.GeneralFunctions.RemoveBoundingQuotes(My.Application.CommandLineArgs(4))
                For i = 0 To My.Application.CommandLineArgs.Count - 1
                    Setup.Log.CmdArgs.Add(My.Application.CommandLineArgs(i))
                Next
            End If

            '----------------------------------------------------------------------------------------------------------------------------
            'query the API and start writing the data
            '----------------------------------------------------------------------------------------------------------------------------
            If HERH.Write() Then
                If HERH.WriteZIP(HERH.DownloadDIR & HERH.ZIPFileName) Then
                    body = HERH.GenerateGoodMailBody()
                    HERH.GoodMail = New clsEmail(Setup)
                    Call HERH.sendGoodEmail("Herhalingstijden historische neerslag", body)
                Else
                    Throw New Exception("")
                End If
            Else
                Throw New Exception("")
            End If

        Catch ex As Exception
            Console.WriteLine(ex.Message)
            Setup.Log.AddError(ex.Message)
            body = HERH.GenerateBadMailBody()
            HERH.BadMail = New clsEmail(Setup)
            Call HERH.sendBadEmail("Herhalingstijden historische neerslag", body)
        End Try

    End Sub
End Module
