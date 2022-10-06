﻿Imports METEOBAS.General
Imports GemBox.Spreadsheet

Public Class clsShapeFile

    Public Path As String
    Public sf As New MapWinGIS.Shapefile

    Private Setup As clsSetup

    Public Sub New(ByRef mySetup As clsSetup)
        Setup = mySetup
    End Sub

    Public Sub New(ByRef mySetup As clsSetup, ByVal myPath As String)
        Setup = mySetup
        Path = myPath
    End Sub

    Public Function Open() As Boolean
        Return sf.Open(Path)
    End Function

    Public Sub Close()
        sf.Close()
    End Sub

    Public Function GetFieldIdx(ByVal Name As String) As Integer
        Dim i As Long
        For i = 0 To sf.NumFields - 1
            If sf.Field(i).Name.Trim.ToUpper = Name.Trim.ToUpper Then Return i
        Next
        Return -1
    End Function

    Public Function getUniqueValuesFromField(ByVal fieldName As String, ByRef Values As List(Of String)) As Boolean
        'this function populates a list with all unique values present in a given field of the underlying shapefile.
        Try
            Dim FieldIdx As Long = GetFieldIdx(fieldName), i As Long
            If FieldIdx < 0 Then Throw New Exception("Error: fieldname " & fieldName & " does not occur in shapefile " & Path)

            For i = 0 To sf.NumShapes - 1
                If Not Values.Contains(sf.CellValue(FieldIdx, i)) Then Values.Add(sf.CellValue(FieldIdx, i))
            Next

            Return True
        Catch ex As Exception
            Me.Setup.Log.AddError(ex.Message)
            Return False
        End Try
    End Function


    Public Function PointDataFromReachDist(ByVal ShapeIdx As Long, ByVal Dist As Double, ByRef X As Double, ByRef Y As Double, ByRef Angle As Double) As Boolean

        'Date: 16-6-2013
        'Author: Siebe Bosch
        'Description: searches for a given shape & distance on the shape the corresponding X- and Y-coordinates as well as the angle of the shape
        'on that particular location
        Dim i As Long

        Try
            Dim myShape As MapWinGIS.Shape
            Dim prevPoint As MapWinGIS.Point, prevDist As Double
            Dim nextPoint As MapWinGIS.Point, nextDist As Double
            myShape = sf.Shape(ShapeIdx)

            prevDist = 0
            nextDist = 0

            For i = 0 To myShape.numPoints - 2
                prevPoint = myShape.Point(i)
                nextPoint = myShape.Point(i + 1)
                nextDist += Math.Sqrt((nextPoint.y - prevPoint.y) ^ 2 + (nextPoint.x - prevPoint.x) ^ 2)

                If nextDist >= Dist Then
                    'interpolate to find the XY-coordinate that belongs to the given distance on the reach
                    X = Setup.GeneralFunctions.Interpolate(prevDist, prevPoint.x, nextDist, nextPoint.x, Dist)
                    Y = Setup.GeneralFunctions.Interpolate(prevDist, prevPoint.y, nextDist, nextPoint.y, Dist)
                    Angle = Me.Setup.GeneralFunctions.LineAngleDegrees(prevPoint.x, prevPoint.y, nextPoint.x, nextPoint.y)
                    Angle = Me.Setup.GeneralFunctions.NormalizeAngle(Angle)
                    Return True
                End If
                prevDist = nextDist

            Next
        Catch ex As Exception
            Me.Setup.Log.AddError("Error in function PointDataFromReachDist.")
            Return False
        End Try


    End Function

End Class

