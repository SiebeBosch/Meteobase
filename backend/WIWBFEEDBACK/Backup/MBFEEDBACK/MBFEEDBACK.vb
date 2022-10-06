Option Explicit On
Imports HYDROC01
Imports System

Module MBFEEDBACK

  'Copyright Siebe Bosch Hydroconsult, 2012
  'Lulofsstraat 55, unit 47 Den Haag, The Netherlands
  'this program handles the contents of a web-based feedback-form.
  'and sends an e-mail with its contents to info@meteobase.nl

  'lokale variabelen
  Dim MailSubject As String
  Dim Question As String
  Dim MailBody As String
  Dim MailTo As String
  Dim QuestionType As String
  Dim Name As String
  Dim Setup As New HYDROC01.General.clsSetup

  Sub Main()
    Dim Debug As Boolean = False
    Console.WriteLine("This program sends the contents of a feedback-form to info@meteobase.nl")

    If Debug Then
      Name = "Siebe Bosch"
      MailTo = "siebe@watercommunicatie.nl"
      QuestionType = "vraag"
      Question = "Kan meteobase ook koffie zetten?"
    Else
      If My.Application.CommandLineArgs.Count = 0 Then
        Console.WriteLine("Enter your name.")
        Name = Setup.General.RemoveBoundingQuotes(Console.ReadLine)
        Console.WriteLine("Enter e-mail address.")
        MailTo = Setup.General.RemoveBoundingQuotes(Console.ReadLine)
        Console.WriteLine("Enter the type of question you have: e.g. question/bug/request.")
        QuestionType = Console.ReadLine
        Console.WriteLine("Enter your message.")
        Question = Console.ReadLine
      ElseIf My.Application.CommandLineArgs.Count <> 4 Then
        Console.WriteLine("Error: incorrect number of arguments presented")
      Else
        Name = Setup.General.RemoveBoundingQuotes(My.Application.CommandLineArgs(0))
        MailTo = Setup.General.RemoveBoundingQuotes(My.Application.CommandLineArgs(1))
        QuestionType = My.Application.CommandLineArgs(2)
        Question = My.Application.CommandLineArgs(3)
      End If
    End If

    'build the content of the e-mail
    MailBody = "Geachte " & Name & "," & vbCrLf
    MailBody &= vbCrLf
    MailBody &= "U hebt het feedbackformulier op de website www.meteobase.nl ingevuld. Onderstaand vindt u een afschrift van uw bericht." & vbCrLf
    MailBody &= "Een kopie van deze e-mail werd verzonden naar info@meteobase.nl." & vbCrLf
    MailBody &= vbCrLf
    MailBody &= "Soort bericht: " & QuestionType & vbCrLf
    MailBody &= "Inhoud bericht:" & vbCrLf
    MailBody &= Question
    MailBody &= vbCrLf
    MailBody &= vbCrLf
    MailBody &= "Wij zullen uw vraag zo spoedig mogelijk beantwoorden." & vbCrLf
    MailBody &= "Met vriendelijke groet," & vbCrLf
    MailBody &= "namens STOWA:" & vbCrLf
    MailBody &= "het meteobase-team." & vbCrLf
    MailBody &= vbCrLf
    MailBody &= "--------------------------------------------" & vbCrLf
    MailBody &= "www.meteobase.nl | het online archief voor de" & vbCrLf
    MailBody &= "watersector van historische neerslag en" & vbCrLf
    MailBody &= "verdamping in Nederland" & vbCrLf
    MailBody &= vbCrLf
    MailBody &= "Aangeboden door STOWA | www.stowa.nl" & vbCrLf
    MailBody &= vbCrLf
    MailBody &= "Mogelijk gemaakt door" & vbCrLf
    MailBody &= "HKV-Lijn in water     | www.hkv.nl" & vbCrLf
    MailBody &= "Hydroconsult          | www.hydroconsult.nl" & vbCrLf
    MailBody &= "--------------------------------------------" & vbCrLf

    'build the e-mail subject
    MailSubject = "Feedbackformulier Meteobase ingevuld: " & QuestionType

    Setup.EMail.SetMeteoBase()      'sets the e-mail as coming from info@meteobase.nl
    Setup.EMail.send(MailTo, MailSubject, MailBody)
    Setup.EMail.send("info@meteobase.nl", MailSubject, MailBody)
  End Sub

End Module

