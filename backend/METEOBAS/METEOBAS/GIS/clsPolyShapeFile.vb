Imports METEOBAS.General
Imports GemBox.Spreadsheet

Public Class clsPolyShapeFile

    Public Path As String
    Public sf As New MapWinGIS.Shapefile

    Public ValueField As String
    Public ValueFieldIdx As Integer = -1

    Private Setup As clsSetup

    Public Sub New(ByRef mySetup As clsSetup, ByVal myPath As String)
        Setup = mySetup
        Path = myPath

    End Sub

    Public Sub New(ByRef mySetup As clsSetup)
        Setup = mySetup
    End Sub

    Public Function SetPath(myPath As String) As Boolean
        Try
            Path = myPath
            Return True
        Catch ex As Exception
            Me.Setup.Log.AddError(ex.Message)
            Return False
        End Try
    End Function

    Public Function Open() As Boolean
        If sf.Open(Path) Then
            Return True
        Else
            Return False
        End If
    End Function

    Public Function Close() As Boolean
        If sf.Close Then
            Return True
        Else
            Return False
        End If
    End Function

    Public Function setValueField(ByVal FieldName As String) As Boolean
        ValueField = FieldName
        ValueFieldIdx = Setup.GISData.getShapeFieldIdxFromFileName(Path, FieldName)
        If ValueFieldIdx >= 0 Then Return True Else Return False
    End Function

End Class
