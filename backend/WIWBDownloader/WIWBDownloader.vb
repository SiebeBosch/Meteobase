Imports System
Imports meteobas.General

Module WIWBDownloader
    Sub Main(args As String())
        Console.WriteLine("Initializing WIWB connection...")
        Dim Setup As New clsSetup
        Dim WIWBRasterData As New METEOBAS.clsWIWBRasterData(Setup)

        Console.WriteLine("Setting download parameters...")
        Dim XMin As Double = 133000
        Dim YMin As Double = 370000
        Dim XMAX As Double = 201000
        Dim YMAX As Double = 370000
        Dim ResultsPath As String = "c:\temp\Rasters.zip"

        Dim fDate As New DateTime(2009, 1, 1)
        Dim tDate As New DateTime(2010, 1, 1)
        Dim fDateInt As Integer = fDate.Year * 10000 + fDate.Month * 100 + fDate.Day
        Dim tDateInt As Integer = tDate.Year * 10000 + tDate.Month * 100 + tDate.Day

        Console.WriteLine("Initiating download...")
        WIWBRasterData.DownloadNSLRastersPre2019(fDateInt, tDateInt, ResultsPath)
        Console.WriteLine("Download complete.")

    End Sub
End Module
