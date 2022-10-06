Option Explicit On

Imports METEOBAS
Imports System
Imports Newtonsoft.Json.Linq

Module WIWBBASIS

    'Copyright Siebe Bosch Hydroconsult, 2012
    'Lulofsstraat 55, unit 47 Den Haag, The Netherlands
    'this program extracts precipitation and/or evaporation data from the WIWB API

    'lokale variabelen
    Dim Setup As New METEOBAS.General.clsSetup
    Dim BasisData As New METEOBAS.clsWIWBBasisData(Setup)

    Sub Main()
        Dim body As String
        Try

            Dim myArg As String
            Console.WriteLine("This program reads precipitation and evaporation data from the WIWB API")

            '----------------------------------------------------------------------------------------------------------------------------
            'system-dependent variables
            '----------------------------------------------------------------------------------------------------------------------------
            If Debugger.IsAttached Then
                BasisData.DownloadURL = "https://www.meteobase.nl/meteobase/downloads/"                                                  'laptop en server
                BasisData.DownloadDIR = "c:\temp\"  'local
            Else
                BasisData.DownloadURL = "https://www.meteobase.nl/meteobase/downloads/"                                                  'laptop en server
                BasisData.DownloadDIR = "c:\Program Files (x86)\PostgreSQL\EnterpriseDB-ApachePHP\apache\www\meteobase\downloads\"  'server
            End If

            If My.Application.CommandLineArgs.Count = 0 Then
                Console.WriteLine("Enter start date (YYYYMMDD)")
                myArg = Console.ReadLine
                BasisData.FDate = myArg
                Setup.Log.CmdArgs.Add(myArg)
                Console.WriteLine("Enter end date (YYYYMMDD)")
                myArg = Console.ReadLine
                BasisData.TDate = myArg
                Setup.Log.CmdArgs.Add(myArg)
                Console.WriteLine("Daily sum stations? (TRUE/FALSE)")
                myArg = Console.ReadLine
                BasisData.Etmaal = myArg
                Setup.Log.CmdArgs.Add(myArg)
                Console.WriteLine("Export precipitation? (TRUE/FALSE)")
                myArg = Console.ReadLine
                BasisData.NSL = myArg
                Setup.Log.CmdArgs.Add(myArg)
                Console.WriteLine("Export Makkink evaporation? (TRUE/FALSE)")
                myArg = Console.ReadLine
                BasisData.MAKKINK = myArg
                Setup.Log.CmdArgs.Add(myArg)
                Console.WriteLine("Enter the session ID")
                myArg = Console.ReadLine
                BasisData.SessionID = myArg
                Setup.Log.CmdArgs.Add(myArg)
                Console.WriteLine("Enter the order number")
                myArg = Console.ReadLine
                BasisData.OrderNum = myArg
                Setup.Log.CmdArgs.Add(myArg)
                Console.WriteLine("Enter the name of the person who orders")
                myArg = Console.ReadLine
                BasisData.Naam = myArg
                Setup.Log.CmdArgs.Add(myArg)
                Console.WriteLine("Enter their e-mailaddress")
                myArg = Console.ReadLine
                BasisData.MailAdres = myArg
                Setup.Log.CmdArgs.Add(myArg)
                Console.WriteLine("Enter the station number")
                myArg = Console.ReadLine
                BasisData.GetAddStationByNumber(myArg)
                Setup.Log.CmdArgs.Add(myArg)

            ElseIf My.Application.CommandLineArgs.Count < 10 Then
                Console.WriteLine("Error: incorrect number of arguments presented")
            Else
                BasisData.FDate = My.Application.CommandLineArgs(0)
                BasisData.TDate = My.Application.CommandLineArgs(1)
                BasisData.Etmaal = Setup.GeneralFunctions.GetBooleanFromString(My.Application.CommandLineArgs(2))
                BasisData.NSL = Setup.GeneralFunctions.GetBooleanFromString(My.Application.CommandLineArgs(3))
                BasisData.MAKKINK = Setup.GeneralFunctions.GetBooleanFromString(My.Application.CommandLineArgs(4))
                BasisData.SessionID = My.Application.CommandLineArgs(5)
                BasisData.OrderNum = My.Application.CommandLineArgs(6)
                BasisData.Naam = Setup.GeneralFunctions.RemoveBoundingQuotes(My.Application.CommandLineArgs(7))
                BasisData.MailAdres = Setup.GeneralFunctions.RemoveBoundingQuotes(My.Application.CommandLineArgs(8))
                For i = 9 To My.Application.CommandLineArgs.Count - 1
                    BasisData.GetAddStationByNumber(My.Application.CommandLineArgs(i))
                Next

                For i = 0 To My.Application.CommandLineArgs.Count - 1
                    Setup.Log.CmdArgs.Add(My.Application.CommandLineArgs(i))
                Next

            End If

            '----------------------------------------------------------------------------------------------------------------------------
            'query the API and start writing the data
            '----------------------------------------------------------------------------------------------------------------------------
            If BasisData.Build() Then  'write the requested datafiles
                body = BasisData.GenerateGoodMailBody()
                BasisData.GoodMail = New clsEmail(Setup)
                Call BasisData.sendGoodEmail("Meteobase bestelling " & BasisData.OrderNum & ": " & "Basisgegevens.", body)
            Else
                Throw New Exception("Error building basisdata.")
            End If
        Catch ex As Exception
            Setup.Log.AddError(ex.Message)
            body = BasisData.GenerateBadMailBody()
            BasisData.BadMail = New clsEmail(Setup)
            Call BasisData.sendBadEmail("Meteobase bestelling " & BasisData.OrderNum & ": " & "Basisgegevens.", body)
        Finally
            Setup.Log.write(BasisData.DownloadDIR & "\" & BasisData.SessionID & "_" & BasisData.OrderNum & ".log", False)
        End Try

    End Sub
End Module
