Option Explicit On
Imports System.Globalization
Imports System.Net.Http
Imports System.Net.Http.Headers
Imports Newtonsoft.Json.Linq
Imports System.Text


Namespace General
    Public Class clsSetup
        Public GISData As clsGISData            'bevat alle shapefiles en rasters!
        Public KNMIData As clsKNMIData          'bevat alle data van het KNMI
        Public MBRasterData As clsMBRasterData  'bevat rasters uit Meteobase
        Public Settings As clsSettings          'de algemene instellingen

        Public GeneralFunctions As GeneralFunctions      'algemene functies

        Public ExcelFile As clsExcelBook        'een workbook in Excel voor resultaten
        Public Log As New clsLog                'de logfile
        Public EMail As clsEmail                'e-mail

        Friend importDir As String              'export directory voor resultaten van bewerkingen

        ''' <summary>
        ''' Constructor
        ''' </summary>
        ''' <remarks></remarks>
        Public Sub New()

            'MsgBox("Hoera, we zitten in de DLL (juiste versie)")

            ' Set dot as decimal point:
            Threading.Thread.CurrentThread.CurrentCulture = CultureInfo.InvariantCulture

            ' Initializeren classes:
            KNMIData = New clsKNMIData(Me)      'bevat alle data van het KNMI
            GISData = New clsGISData(Me)

            Settings = New clsSettings()      'de algemene instellingen
            ExcelFile = New clsExcelBook()
            GeneralFunctions = New GeneralFunctions(Me)  'algemene functies
            EMail = New clsEmail(Me)

        End Sub

        Public Async Function GetAccessToken(clientId As String, clientSecret As String) As Task(Of String)
            'Dim clientId As String = "api-wiwb-demo"
            'Dim clientSecret As String = "895269fb-ca52-429e-af0b-96a9f0266e71"
            Dim tokenEndpoint As String = "https://login.hydronet.com/auth/realms/hydronet/protocol/openid-connect/token"

            Using httpClient As New HttpClient()
                httpClient.DefaultRequestHeaders.Accept.Add(New MediaTypeWithQualityHeaderValue("application/json"))

                Dim content As New FormUrlEncodedContent(New Dictionary(Of String, String) From {
                    {"grant_type", "client_credentials"},
                    {"client_id", clientId},
                    {"client_secret", clientSecret}
                })

                Dim response As HttpResponseMessage = Await httpClient.PostAsync(tokenEndpoint, content)

                If response.IsSuccessStatusCode Then
                    Dim jsonResponse As String = Await response.Content.ReadAsStringAsync()
                    Dim tokenData As JObject = JObject.Parse(jsonResponse)
                    Dim accessToken As String = tokenData("access_token").ToString()
                    Return accessToken
                Else
                    Throw New Exception($"Error retrieving access token. Status code: {response.StatusCode}")
                End If
            End Using
        End Function

        Function IsAccessTokenValid(accessToken As String, Optional validityBuffer As Integer = 30) As Boolean
            Dim tokenParts As String() = accessToken.Split("."c)
            If tokenParts.Length <> 3 Then
                Return False
            End If

            Dim payload As String = tokenParts(1)
            Dim payloadJson As String = Encoding.UTF8.GetString(Base64UrlDecode(payload))
            Dim payloadData As JObject = JObject.Parse(payloadJson)
            Dim expirationTime As Long = payloadData("exp").ToObject(Of Long)()

            Dim currentTime As Long = DateTimeOffset.UtcNow.ToUnixTimeSeconds()
            Dim bufferInSeconds As Long = validityBuffer * 60 ' Convert minutes to seconds
            Return currentTime + bufferInSeconds < expirationTime
        End Function


        Private Function Base64UrlDecode(base64Url As String) As Byte()
            Dim padding As Integer = base64Url.Length Mod 4
            If padding > 0 Then
                base64Url += New String("="c, 4 - padding)
            End If
            base64Url = base64Url.Replace("-"c, "+"c).Replace("_"c, "/"c)
            Return Convert.FromBase64String(base64Url)
        End Function



        Public Sub PassAreaShape(ByVal sfArea As MapWinGIS.Shapefile)
            Me.GISData.SubcatchmentShapeFile.Initialize(sfArea.Filename)
            Me.GISData.SubcatchmentShapeFile.PolySF.sf = sfArea
        End Sub

        Public Sub SetImportDir(ByVal myImportDir As String)
            Me.importDir = myImportDir
        End Sub
        Public Sub addError(ByVal myError As String)
            Me.Log.AddError(myError)
        End Sub
        Public Sub addWarning(ByVal myWarning As String)
            Me.Log.AddWarning(myWarning)
        End Sub

        Public Function ReadMeteoBasePrecHourly(ByVal myPath As String) As Boolean
            KNMIData = New clsKNMIData(Me)
            Call KNMIData.readMBTextFile(myPath)
            Return True
        End Function

        Public Function ExportMeteoBasePrecHourly() As Boolean
            If KNMIData.writeMBTextFiles(Me.importDir) Then
                Return True
            Else
                Return False
            End If
        End Function

        Public Function CalcMeteoBaseHourlyStats(ByVal myArea As Double) As Boolean
            If KNMIData.calcHourlyEventStats(myArea) Then
                Return True
            Else
                Return False
            End If
        End Function
        Public Sub WriteExcelFile(ByVal myPath As String)
            Me.ExcelFile.Path = myPath
            Call Me.ExcelFile.Save()
        End Sub

    End Class
End Namespace