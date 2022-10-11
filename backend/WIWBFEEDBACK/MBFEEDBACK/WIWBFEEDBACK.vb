Option Explicit On
Imports METEOBAS
Imports System
Imports METEOBAS.General
Imports MimeKit

Friend Module WIWBFEEDBACK

    'Copyright Siebe Bosch Hydroconsult, 2012
    'Lulofsstraat 55, unit 47 Den Haag, The Netherlands
    'this program handles the contents of a web-based feedback-form.
    'and sends an e-mail with its contents to info@meteobase.nl

    'lokale variabelen
    Private Question As String
    Private MailBody As String
    Private MailTo As String
    Private QuestionType As String
    Private Name As String
    Private Setup As New clsSetup

    Dim EmailPassword As String
    Public FeedbackMail As clsEmail


    Public Sub Main()
        Dim CommandLineArgs As String = ""
        Try
            Setup = New clsSetup
            Console.WriteLine("This program sends the contents of a feedback-form to info@meteobase.nl")
            EmailPassword = Setup.GeneralFunctions.GetEmailPasswordFromFile("c:\GITHUB\Meteobase\backend\licenses\email.txt", My.Application.Info.DirectoryPath & "\licenses\email.txt")
            FeedbackMail = New clsEmail(Setup)

            If Debugger.IsAttached Then
                Name = "Siebe Bosch"
                MailTo = "siebe@hydroconsult.nl"
                QuestionType = "vraag"
                Question = "Yooo tell me what I want, wat I really really want! http://www.google.com"
            Else
                If My.Application.CommandLineArgs.Count = 0 Then
                    Console.WriteLine("Enter your name.")
                    Name = Setup.GeneralFunctions.RemoveBoundingQuotes(Console.ReadLine)
                    CommandLineArgs &= Name & " "
                    Console.WriteLine("Enter e-mail address.")
                    MailTo = Setup.GeneralFunctions.RemoveBoundingQuotes(Console.ReadLine)
                    CommandLineArgs &= MailTo & " "
                    Console.WriteLine("Enter the type of question you have: e.g. question/bug/request.")
                    QuestionType = Console.ReadLine
                    CommandLineArgs &= QuestionType & " "
                    Console.WriteLine("Enter your message.")
                    Question = Console.ReadLine
                    CommandLineArgs &= Replace(Replace(Question, "http", "---webaddress removed---"), "www", "---webaddress removed---") & " "
                ElseIf My.Application.CommandLineArgs.Count <> 4 Then
                    Console.WriteLine("Error: incorrect number of arguments presented")
                Else
                    Name = Setup.GeneralFunctions.RemoveBoundingQuotes(My.Application.CommandLineArgs(0))
                    MailTo = Setup.GeneralFunctions.RemoveBoundingQuotes(My.Application.CommandLineArgs(1))
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
            MailBody &= "Naam: " & Name & vbCrLf
            MailBody &= "E-mailadres: " & MailTo & vbCrLf
            MailBody &= "Soort bericht: " & QuestionType & vbCrLf
            MailBody &= "Inhoud bericht:" & vbCrLf
            MailBody &= Replace(Replace(Question, "http", "---webaddress not allowed---"), "www", "---webaddress not allowed---")
            MailBody &= vbCrLf
            MailBody &= vbCrLf
            MailBody &= "Wij zullen uw vraag zo spoedig mogelijk beantwoorden." & vbCrLf
            MailBody &= "Met vriendelijke groet," & vbCrLf
            MailBody &= "namens Het Waterschapshuis:" & vbCrLf
            MailBody &= "het meteobase-team." & vbCrLf
            MailBody &= vbCrLf
            MailBody &= "--------------------------------------------" & vbCrLf
            MailBody &= "www.meteobase.nl | het online archief voor de" & vbCrLf
            MailBody &= "watersector van historische neerslag en" & vbCrLf
            MailBody &= "verdamping in Nederland" & vbCrLf
            MailBody &= vbCrLf
            MailBody &= "Aangeboden door Het Waterschapshuis | www.hetwaterschapshuis.nl" & vbCrLf
            MailBody &= vbCrLf
            MailBody &= "Mogelijk gemaakt door" & vbCrLf
            MailBody &= "Hydrologic            | www.hydrologic.nl" & vbCrLf
            MailBody &= "HKV-Lijn in water     | www.hkv.nl" & vbCrLf
            MailBody &= "Hydroconsult          | www.hydroconsult.nl" & vbCrLf
            MailBody &= "--------------------------------------------" & vbCrLf
            SendEmail("Feedbackformulier Meteobase ingevuld: " & QuestionType, MailBody)

        Catch ex As Exception

        End Try


    End Sub

    Public Function SendEmail(header As String, body As String) As Boolean
        Try
            'eerst naar de aanvrager zelf
            If Not FeedbackMail.Send(EmailPassword, MailTo, Name, header, body) Then
                Setup.Log.AddError("Verzenden e-mail is niet gelukt. Neem a.u.b. contact met ons op via info@meteobase.nl.")
            End If

            'dan naar onszelf
            If Not FeedbackMail.Send(EmailPassword, "info@meteobase.nl", "Meteobase", header, body) Then
                Setup.Log.AddError("Verzenden e-mail is niet gelukt. Neem a.u.b. contact met ons op via info@meteobase.nl.")
            End If
            Return True
        Catch ex As Exception
            Setup.Log.AddError(ex.Message)
            Return False
        End Try

    End Function

    Public Enum enmReturnCode
        ok = 1
        invalidrecipient = 1
        russianemail = 2
        containswebadress = 3
        untrustworthyname = 4
    End Enum


    Public Function ValidMail(Name As String, MailTo As String, ByVal UserMessage As String, ByRef MailBody As String, ByRef ReturnCode As enmReturnCode) As Boolean
        Try
            If Setup.GeneralFunctions.ConsonantsInARow(Name) > 5 Then
                ReturnCode = enmReturnCode.untrustworthyname
                Throw New Exception("Ongeloofwaardige naam opgegeven.")
            End If
            If InStr(MailTo, "@") <= 0 Then
                ReturnCode = enmReturnCode.invalidrecipient
                Throw New Exception("Geen geldig e-mailadres opgegeven.")
            End If
            If Strings.Right(MailTo, 3).Trim.ToUpper = ".RU" Then
                ReturnCode = enmReturnCode.russianemail
                Throw New Exception("Russisch e-mailadres opgegeven.")
            End If
            If InStr(UserMessage, "https://") > 0 Then
                ReturnCode = enmReturnCode.containswebadress
                Throw New Exception("In het bericht werd een https webadres vermeld.")     'het noemen van webadressen in het bericht is om veiligheidsredenen niet toegestaan
            End If
            If InStr(UserMessage, "http://") > 0 Then
                ReturnCode = enmReturnCode.containswebadress
                Throw New Exception("In het bericht werd een http webadres vermeld.")     'het noemen van webadressen in het bericht is om veiligheidsredenen niet toegestaan
            End If
            If InStr(UserMessage, "www.") > 0 Then
                ReturnCode = enmReturnCode.containswebadress
                Throw New Exception("In het bericht werd een www webadres vermeld.")     'het noemen van webadressen in het bericht is om veiligheidsredenen niet toegestaan
            End If
            ReturnCode = enmReturnCode.ok
            Return True
        Catch ex As Exception
            MailBody = "Error in function ValidMail: " & ex.Message & vbCrLf & vbCrLf & MailBody
        Return False
        End Try
    End Function

End Module

