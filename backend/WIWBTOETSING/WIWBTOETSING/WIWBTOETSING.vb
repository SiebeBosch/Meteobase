Option Explicit On

Imports METEOBAS
Imports System

Module WIWBTOETSING

    'Copyright Siebe Bosch Hydroconsult, 2012
    'Lulofsstraat 55, unit 47 Den Haag, The Netherlands
    'this program extracts precipitation and/or evaporation data from the Meteobase database (PostgreSQL)

    'lokale variabelen
    Dim Setup As New METEOBAS.General.clsSetup
    Dim ToetsData As New METEOBAS.clsWIWBToetsData(Setup)

    Sub Main()

        Dim myArg As String
        Console.WriteLine("This program reads precipitation and evaporation data from the Meteobase database")

        '----------------------------------------------------------------------------------------------------------------------------
        'system-dependent variables
        '----------------------------------------------------------------------------------------------------------------------------
        If Debugger.IsAttached Then
            ToetsData.DownloadURL = "https://www.meteobase.nl/meteobase/downloads/"                                                 'laptop en server
            ToetsData.DownloadDIR = "c:\temp"                                                                                   'laptop
            ToetsData.FilesDir = "v:\PROJECTEN\H0069.Meteobase\01.Klimaatreeksen14\"
        Else
            ToetsData.DownloadURL = "https://www.meteobase.nl/meteobase/downloads/"                                              'laptop en server
            ToetsData.DownloadDIR = "c:\Program Files (x86)\PostgreSQL\EnterpriseDB-ApachePHP\apache\www\meteobase\downloads\"  'server
            ToetsData.FilesDir = "c:\Apache24\htdocs\meteobase\downloads\fixed\"  'server
        End If

        If My.Application.CommandLineArgs.Count = 0 Then
            Console.WriteLine("Export 2015 series? (TRUE/FALSE)")
            myArg = Console.ReadLine
            ToetsData.STATS2015 = myArg
            Setup.Log.CmdArgs.Add(myArg)
            Console.WriteLine("Export 2019 series? (TRUE/FALSE)")
            myArg = Console.ReadLine
            ToetsData.STATS2019 = myArg
            Setup.Log.CmdArgs.Add(myArg)
            Console.WriteLine("Export Huidig? (TRUE/FALSE)")
            myArg = Console.ReadLine
            ToetsData.HUIDIG = myArg
            Setup.Log.CmdArgs.Add(myArg)
            Console.WriteLine("Export 2030? (TRUE/FALSE)")
            myArg = Console.ReadLine
            ToetsData.ALL_2030 = myArg
            Setup.Log.CmdArgs.Add(myArg)
            Console.WriteLine("Export 2050 GL? (TRUE/FALSE)")
            myArg = Console.ReadLine
            ToetsData.GL_2050 = myArg
            Setup.Log.CmdArgs.Add(myArg)
            Console.WriteLine("Export 2050 GH? (TRUE/FALSE)")
            myArg = Console.ReadLine
            ToetsData.GH_2050 = myArg
            Setup.Log.CmdArgs.Add(myArg)
            Console.WriteLine("Export 2050 WL? (TRUE/FALSE)")
            myArg = Console.ReadLine
            ToetsData.WL_2050 = myArg
            Setup.Log.CmdArgs.Add(myArg)
            Console.WriteLine("Export 2050 WH? (TRUE/FALSE)")
            myArg = Console.ReadLine
            ToetsData.WH_2050 = myArg
            Setup.Log.CmdArgs.Add(myArg)
            Console.WriteLine("Export 2085 GL? (TRUE/FALSE)")
            myArg = Console.ReadLine
            ToetsData.GL_2085 = myArg
            Setup.Log.CmdArgs.Add(myArg)
            Console.WriteLine("Export 2085 GH? (TRUE/FALSE)")
            myArg = Console.ReadLine
            ToetsData.GH_2085 = myArg
            Setup.Log.CmdArgs.Add(myArg)
            Console.WriteLine("Export 2085 WL? (TRUE/FALSE)")
            myArg = Console.ReadLine
            ToetsData.WL_2085 = myArg
            Setup.Log.CmdArgs.Add(myArg)
            Console.WriteLine("Export 2085 WH? (TRUE/FALSE)")
            myArg = Console.ReadLine
            ToetsData.WH_2085 = myArg
            Setup.Log.CmdArgs.Add(myArg)
            Console.WriteLine("Enter the session ID")
            myArg = Console.ReadLine
            ToetsData.SessionID = myArg
            Setup.Log.CmdArgs.Add(myArg)
            Console.WriteLine("Enter the order number")
            myArg = Console.ReadLine
            ToetsData.OrderNum = myArg
            Setup.Log.CmdArgs.Add(myArg)
            Console.WriteLine("Enter the name of the person who orders")
            myArg = Console.ReadLine
            ToetsData.Naam = myArg
            Setup.Log.CmdArgs.Add(myArg)
            Console.WriteLine("Enter their e-mailaddress")
            myArg = Console.ReadLine
            ToetsData.MailAdres = myArg
            Setup.Log.CmdArgs.Add(myArg)

        ElseIf My.Application.CommandLineArgs.Count <> 16 Then
            Console.WriteLine("Error: incorrect number of arguments presented")
            Setup.Log.AddError("Error: incorrect number of arguments presented to the executable.")
        Else
            ToetsData.STATS2015 = Setup.GeneralFunctions.GetBooleanFromString(My.Application.CommandLineArgs(0))
            ToetsData.STATS2019 = Setup.GeneralFunctions.GetBooleanFromString(My.Application.CommandLineArgs(1))
            ToetsData.HUIDIG = Setup.GeneralFunctions.GetBooleanFromString(My.Application.CommandLineArgs(2))
            ToetsData.ALL_2030 = Setup.GeneralFunctions.GetBooleanFromString(My.Application.CommandLineArgs(3))
            ToetsData.GL_2050 = Setup.GeneralFunctions.GetBooleanFromString(My.Application.CommandLineArgs(4))
            ToetsData.GH_2050 = Setup.GeneralFunctions.GetBooleanFromString(My.Application.CommandLineArgs(5))
            ToetsData.WL_2050 = Setup.GeneralFunctions.GetBooleanFromString(My.Application.CommandLineArgs(6))
            ToetsData.WH_2050 = Setup.GeneralFunctions.GetBooleanFromString(My.Application.CommandLineArgs(7))
            ToetsData.GL_2085 = Setup.GeneralFunctions.GetBooleanFromString(My.Application.CommandLineArgs(8))
            ToetsData.GH_2085 = Setup.GeneralFunctions.GetBooleanFromString(My.Application.CommandLineArgs(9))
            ToetsData.WL_2085 = Setup.GeneralFunctions.GetBooleanFromString(My.Application.CommandLineArgs(10))
            ToetsData.WH_2085 = Setup.GeneralFunctions.GetBooleanFromString(My.Application.CommandLineArgs(11))
            ToetsData.SessionID = My.Application.CommandLineArgs(12)
            ToetsData.OrderNum = My.Application.CommandLineArgs(13)
            ToetsData.Naam = Setup.GeneralFunctions.RemoveBoundingQuotes(My.Application.CommandLineArgs(14))
            ToetsData.MailAdres = Setup.GeneralFunctions.RemoveBoundingQuotes(My.Application.CommandLineArgs(15))

            'keep track of the command line arguments for feedback
            Setup.Log.CmdArgs.Add(My.Application.CommandLineArgs(0))
            Setup.Log.CmdArgs.Add(My.Application.CommandLineArgs(1))
            Setup.Log.CmdArgs.Add(My.Application.CommandLineArgs(2))
            Setup.Log.CmdArgs.Add(My.Application.CommandLineArgs(3))
            Setup.Log.CmdArgs.Add(My.Application.CommandLineArgs(4))
            Setup.Log.CmdArgs.Add(My.Application.CommandLineArgs(5))
            Setup.Log.CmdArgs.Add(My.Application.CommandLineArgs(6))
            Setup.Log.CmdArgs.Add(My.Application.CommandLineArgs(7))
            Setup.Log.CmdArgs.Add(My.Application.CommandLineArgs(8))
            Setup.Log.CmdArgs.Add(My.Application.CommandLineArgs(9))
            Setup.Log.CmdArgs.Add(My.Application.CommandLineArgs(10))
            Setup.Log.CmdArgs.Add(My.Application.CommandLineArgs(11))
            Setup.Log.CmdArgs.Add(My.Application.CommandLineArgs(12))
            Setup.Log.CmdArgs.Add(My.Application.CommandLineArgs(13))
            Setup.Log.CmdArgs.Add(My.Application.CommandLineArgs(14))
            Setup.Log.CmdArgs.Add(My.Application.CommandLineArgs(15))

        End If

        '----------------------------------------------------------------------------------------------------------------------------
        'query the database and start writing the data
        '----------------------------------------------------------------------------------------------------------------------------
        Dim body As String
        Try
            If ToetsData.Build() Then  'add the requested datafiles to a zipfile
                body = ToetsData.GenerateGoodMailBody()
                ToetsData.GoodMail = New clsEmail(Setup)
                Call ToetsData.sendGoodEmail("Meteobase bestelling " & ToetsData.OrderNum & ": " & "Toetsingsdata.", body)
            Else
                Throw New Exception("Error building Toetsingsdata.")
            End If
        Catch ex As Exception
            Setup.Log.AddError(ex.Message)
            body = ToetsData.GenerateBadMailBody()
            ToetsData.BadMail = New clsEmail(Setup)
            Call ToetsData.sendBadEmail("Meteobase bestelling " & ToetsData.OrderNum & ": " & "Toetsingsdata.", body)
        End Try

    End Sub

End Module
