Option Explicit On

Imports METEOBAS
Imports System

Module WIWBSTEDELIJK

    'Copyright Siebe Bosch Hydroconsult, 2020
    'Lulofsstraat 55, unit 47 Den Haag, The Netherlands
    'this program extracts precipitation and/or evaporation data from the Meteobase database (PostgreSQL)

    'lokale variabelen
    Dim Setup As New METEOBAS.General.clsSetup
    Dim StochData As New METEOBAS.clsWIWBStedData(Setup)
    Sub Main()

        Dim myArg As String
        Console.WriteLine("This program reads precipitation and evaporation data from the Meteobase database")

        '----------------------------------------------------------------------------------------------------------------------------
        'system-dependent variables
        '----------------------------------------------------------------------------------------------------------------------------
        If Debugger.IsAttached Then
            StochData.DownloadURL = "https://www.meteobase.nl/meteobase/downloads/"                                           'laptop en server
            StochData.DownloadDIR = "c:\temp"                                                                                   'laptop
            StochData.FilesDir = "c:\Dropbox\MeteoBase\MBSTOCHASTEN\"
        Else
            StochData.DownloadURL = "https://www.meteobase.nl/meteobase/downloads/"                                                  'laptop en server
            StochData.DownloadDIR = "c:\Program Files (x86)\PostgreSQL\EnterpriseDB-ApachePHP\apache\www\meteobase\downloads\"      'server
            StochData.FilesDir = "c:\Apache24\htdocs\meteobase\downloads\fixed\"  'server
        End If

        If My.Application.CommandLineArgs.Count = 0 Then
            Console.WriteLine("Export Events for climate 2014? (TRUE/FALSE)")
            myArg = Console.ReadLine
            StochData.Events2014 = myArg
            Setup.Log.CmdArgs.Add(myArg)
            Console.WriteLine("Export Events for climate 2030? (TRUE/FALSE)")
            myArg = Console.ReadLine
            StochData.Events2030 = myArg
            Setup.Log.CmdArgs.Add(myArg)
            Console.WriteLine("Export Events for climate 2050? (TRUE/FALSE)")
            myArg = Console.ReadLine
            StochData.Events2050 = myArg
            Setup.Log.CmdArgs.Add(myArg)
            Console.WriteLine("Export Events for climate 2085? (TRUE/FALSE)")
            myArg = Console.ReadLine
            StochData.Events2085 = myArg
            Setup.Log.CmdArgs.Add(myArg)
            Console.WriteLine("Enter the session ID")
            myArg = Console.ReadLine
            StochData.SessionID = myArg
            Setup.Log.CmdArgs.Add(myArg)
            Console.WriteLine("Enter the order number")
            myArg = Console.ReadLine
            StochData.OrderNum = myArg
            Setup.Log.CmdArgs.Add(myArg)
            Console.WriteLine("Enter the name of the person who orders")
            myArg = Console.ReadLine
            StochData.Naam = myArg
            Setup.Log.CmdArgs.Add(myArg)
            Console.WriteLine("Enter their e-mailaddress")
            myArg = Console.ReadLine
            StochData.MailAdres = myArg
            Setup.Log.CmdArgs.Add(myArg)
        ElseIf My.Application.CommandLineArgs.Count <> 8 Then
            Console.WriteLine("Error: incorrect number of arguments presented")
            Setup.Log.AddError("Error: incorrect number of arguments presented to the executable.")
        Else
            StochData.Events2014 = Setup.GeneralFunctions.GetBooleanFromString(My.Application.CommandLineArgs(0))
            StochData.Events2030 = Setup.GeneralFunctions.GetBooleanFromString(My.Application.CommandLineArgs(1))
            StochData.Events2050 = Setup.GeneralFunctions.GetBooleanFromString(My.Application.CommandLineArgs(2))
            StochData.Events2085 = Setup.GeneralFunctions.GetBooleanFromString(My.Application.CommandLineArgs(3))
            StochData.SessionID = My.Application.CommandLineArgs(4)
            StochData.OrderNum = My.Application.CommandLineArgs(5)
            StochData.Naam = Setup.GeneralFunctions.RemoveBoundingQuotes(My.Application.CommandLineArgs(6))
            StochData.MailAdres = Setup.GeneralFunctions.RemoveBoundingQuotes(My.Application.CommandLineArgs(7))
        End If

        '----------------------------------------------------------------------------------------------------------------------------
        'query the database and start writing the data
        '----------------------------------------------------------------------------------------------------------------------------
        Dim body As String
        Try
            If StochData.Build() Then  'add the requested datafiles to a zipfile
                body = StochData.GenerateGoodMailBody()
                StochData.GoodMail = New clsEmail(Setup)
                Call StochData.sendGoodEmail("Meteobase bestelling " & StochData.OrderNum & ": Stedelijke neerslaggebeurtenissen.", body)
            Else
                Throw New Exception("Error building Stochastic data.")
            End If
        Catch ex As Exception
            Setup.Log.AddError(ex.Message)
            body = StochData.GenerateBadMailBody()
            StochData.BadMail = New clsEmail(Setup)
            Call StochData.sendBadEmail("Meteobase bestelling " & StochData.OrderNum & ": Stedelijke neerslaggebeurtenissen.", body)
        End Try

    End Sub

End Module
