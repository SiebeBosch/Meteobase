Option Explicit On

Imports METEOBAS
Imports System

Module WIWBSTOCHASTEN

    'Copyright Siebe Bosch Hydroconsult, 2012
    'Lulofsstraat 55, unit 47 Den Haag, The Netherlands
    'this program extracts precipitation and/or evaporation data from the Meteobase database (PostgreSQL)

    'lokale variabelen
    Dim Setup As New METEOBAS.General.clsSetup
    Dim StochData As New METEOBAS.clsWIWBStochData(Setup)
    Sub Main()

        Dim myArg As String
        Console.WriteLine("This program reads precipitation and evaporation data from the Meteobase database")

        '----------------------------------------------------------------------------------------------------------------------------
        'system-dependent variables
        '----------------------------------------------------------------------------------------------------------------------------
        If Debugger.IsAttached Then
            StochData.DownloadURL = "https://www.meteobase.nl/meteobase/downloads/"                                          'laptop en server
            StochData.DownloadDIR = "c:\temp"                                                                                   'laptop
            StochData.FilesDir = "c:\Dropbox\MeteoBase\MBSTOCHASTEN\"
        Else
            StochData.DownloadURL = "https://www.meteobase.nl/meteobase/downloads/"                                                 'laptop en server
            StochData.DownloadDIR = "c:\Apache24\htdocs\meteobase\downloads\"      'server
            StochData.FilesDir = "c:\Apache24\htdocs\meteobase\downloads\fixed\"  'server
        End If

        If My.Application.CommandLineArgs.Count = 0 Then
            Console.WriteLine("Export Statistics 2015? (TRUE/FALSE)")
            myArg = Console.ReadLine
            StochData.Stats2015 = myArg
            Setup.Log.CmdArgs.Add(myArg)
            Console.WriteLine("Export Statistics 2019? (TRUE/FALSE)")
            myArg = Console.ReadLine
            StochData.Stats2019 = myArg
            Setup.Log.CmdArgs.Add(myArg)
            Console.WriteLine("Export Statistics 2024? (TRUE/FALSE)")
            myArg = Console.ReadLine
            StochData.Stats2024 = myArg
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
        ElseIf My.Application.CommandLineArgs.Count <> 7 Then
            Console.WriteLine("Error: incorrect number of arguments presented")
            Setup.Log.AddError("Error: incorrect number of arguments presented to the executable.")
        Else
            StochData.Stats2015 = Setup.GeneralFunctions.GetBooleanFromString(My.Application.CommandLineArgs(0))
            StochData.Stats2019 = Setup.GeneralFunctions.GetBooleanFromString(My.Application.CommandLineArgs(1))
            StochData.Stats2024 = Setup.GeneralFunctions.GetBooleanFromString(My.Application.CommandLineArgs(2))
            StochData.SessionID = My.Application.CommandLineArgs(3)
            StochData.OrderNum = My.Application.CommandLineArgs(4)
            StochData.Naam = Setup.GeneralFunctions.RemoveBoundingQuotes(My.Application.CommandLineArgs(5))
            StochData.MailAdres = Setup.GeneralFunctions.RemoveBoundingQuotes(My.Application.CommandLineArgs(6))
        End If

        '----------------------------------------------------------------------------------------------------------------------------
        'query the database and start writing the data
        '----------------------------------------------------------------------------------------------------------------------------
        Dim body As String
        Try
            If StochData.Build() Then  'add the requested datafiles to a zipfile
                body = StochData.GenerateGoodMailBody()
                StochData.GoodMail = New clsEmail(Setup)
                Call StochData.sendGoodEmail("Meteobase bestelling " & StochData.OrderNum & ": " & "Stochastentabellen.", body)
            Else
                Throw New Exception("Error building Stochastic data.")
            End If
        Catch ex As Exception
            Setup.Log.AddError(ex.Message)
            body = StochData.GenerateBadMailBody()
            StochData.BadMail = New clsEmail(Setup)
            Call StochData.sendBadEmail("Meteobase bestelling " & StochData.OrderNum & ": " & "Stochastentabellen", body)
        End Try

    End Sub

End Module
