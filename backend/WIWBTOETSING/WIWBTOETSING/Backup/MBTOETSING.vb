Option Explicit On

Imports HYDROC01
Imports System

Module MBTOETSING

  'Copyright Siebe Bosch Hydroconsult, 2012
  'Lulofsstraat 55, unit 47 Den Haag, The Netherlands
  'this program extracts precipitation and/or evaporation data from the Meteobase database (PostgreSQL)

  'lokale variabelen
  Dim Setup As New HYDROC01.General.clsSetup
  Dim ToetsData As New HYDROC01.clsMBToetsData(Setup)

  Sub Main()

    Dim RunOnServer As Boolean = True

    Dim myArg As String
    Console.WriteLine("This program reads precipitation and evaporation data from the Meteobase database")

    '----------------------------------------------------------------------------------------------------------------------------
    'system-dependent variables
    '----------------------------------------------------------------------------------------------------------------------------
    If RunOnServer Then
      ToetsData.DownloadURL = "http://62.148.170.210/meteobase/downloads/"                                                 'laptop en server
      ToetsData.DownloadDIR = "c:\Program Files (x86)\PostgreSQL\EnterpriseDB-Apache\Php\apache\www\meteobase\downloads\"  'server
      ToetsData.FilesDir = "c:\Program Files (x86)\PostgreSQL\EnterpriseDB-Apache\Php\apache\www\meteobase\downloads\fixed\"  'server
    Else
      ToetsData.DownloadURL = "http://62.148.170.210/meteobase/downloads/"                                                 'laptop en server
      ToetsData.DownloadDIR = "c:\temp"                                                                                   'laptop
      ToetsData.FilesDir = "v:\PROJECTEN\H0069.Meteobase\01.Klimaatreeksen14\"
    End If

    If My.Application.CommandLineArgs.Count = 0 Then
      Console.WriteLine("Export Huidig? (TRUE/FALSE)")
      myArg = Console.ReadLine
      ToetsData.huidig = myArg
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

    ElseIf My.Application.CommandLineArgs.Count <> 14 Then
      Console.WriteLine("Error: incorrect number of arguments presented")
      Setup.Log.AddError("Error: incorrect number of arguments presented to the executable.")
    Else
      ToetsData.HUIDIG = Setup.GeneralFunctions.GetBooleanFromString(My.Application.CommandLineArgs(0))
      ToetsData.ALL_2030 = Setup.GeneralFunctions.GetBooleanFromString(My.Application.CommandLineArgs(1))
      ToetsData.GL_2050 = Setup.GeneralFunctions.GetBooleanFromString(My.Application.CommandLineArgs(2))
      ToetsData.GH_2050 = Setup.GeneralFunctions.GetBooleanFromString(My.Application.CommandLineArgs(3))
      ToetsData.WL_2050 = Setup.GeneralFunctions.GetBooleanFromString(My.Application.CommandLineArgs(4))
      ToetsData.WH_2050 = Setup.GeneralFunctions.GetBooleanFromString(My.Application.CommandLineArgs(5))
      ToetsData.GL_2085 = Setup.GeneralFunctions.GetBooleanFromString(My.Application.CommandLineArgs(6))
      ToetsData.GH_2085 = Setup.GeneralFunctions.GetBooleanFromString(My.Application.CommandLineArgs(7))
      ToetsData.WL_2085 = Setup.GeneralFunctions.GetBooleanFromString(My.Application.CommandLineArgs(8))
      ToetsData.WH_2085 = Setup.GeneralFunctions.GetBooleanFromString(My.Application.CommandLineArgs(9))
      ToetsData.SessionID = My.Application.CommandLineArgs(10)
      ToetsData.OrderNum = My.Application.CommandLineArgs(11)
      ToetsData.Naam = Setup.GeneralFunctions.RemoveBoundingQuotes(My.Application.CommandLineArgs(12))
      ToetsData.MailAdres = Setup.GeneralFunctions.RemoveBoundingQuotes(My.Application.CommandLineArgs(13))
    End If

    '----------------------------------------------------------------------------------------------------------------------------
    'query the database and start writing the data
    '----------------------------------------------------------------------------------------------------------------------------
    Try
      If ToetsData.Build() Then  'add the requested datafiles to a zipfile
        Call ToetsData.InitializeGoodMail("Toetsingsgegevens Meteobase")
        Call ToetsData.sendGoodEmail()
      Else
        Call ToetsData.InitializeBadMail("Toetsingsgegevens Meteobase")
        Call ToetsData.sendBadEmail()
      End If
    Catch ex As Exception
      Setup.Log.AddError(ex.Message)
      Call ToetsData.InitializeBadMail("Toetsingsgegevens Meteobase")
      Call ToetsData.sendBadEmail()
    End Try

  End Sub

End Module
