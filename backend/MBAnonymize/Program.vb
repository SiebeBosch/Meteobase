Imports System
Imports System.IO
Imports Npgsql

Module Program
    Sub Main(args As String())

        Try
            Console.WriteLine("Aonymizing Meteobase data...")
            Dim Path As String
            Dim ConnString As String

            If Debugger.IsAttached Then
                Path = "c:\GITHUB\Meteobase\backend\licenses\anonymizer.txt"
            Else
                Path = "d:\Dropbox\MeteoBase\BACKEND\Licenses\anonymizer.txt"
            End If
            Console.WriteLine("Path to connection string: " & Path)

            If Not System.IO.File.Exists(Path) Then Throw New Exception("Configuration file not found: " & Path)

            Using configReader As New StreamReader(Path)
                ConnString = configReader.ReadLine.Trim
                Console.WriteLine("Connetion string set to: " & ConnString)
            End Using

            Using conn As New NpgsqlConnection(ConnString)
                conn.Open()
                Console.WriteLine("Database connection successfully opened.")
                'Dim query As String = "UPDATE public.tbaanvrager SET naam = SHA2(naam, 256), email = 'X' || RIGHT(email, LENGTH(email) - POSITION('@' IN email)) WHERE logindatum < now() - interval '1 month';"
                Dim query As String = "UPDATE public.tbaanvrager SET naam = 'XXX', email = 'X' || RIGHT(email, LENGTH(email) - POSITION('@' IN email)) WHERE to_timestamp(logindatum, 'YYYY-MM-DD HH24:MI:SS') < now() - interval '1 month';"
                Console.WriteLine("Query: " & query)
                Using cmd As New NpgsqlCommand(query, conn)
                    Dim nRows As Integer = cmd.ExecuteNonQuery()
                    Console.WriteLine("Number of rows affected: " & nRows)
                End Using
                conn.Close()
                Console.WriteLine("Database connection closed.")
            End Using

            Debug.WriteLine("Operation complete.")

        Catch ex As Exception
            Debug.WriteLine("Operation failed.")
            Console.WriteLine(ex.Message)
        End Try

    End Sub


    Public Function Read() As Boolean
        Try
            Return True
        Catch ex As Exception
            MsgBox("Error in function Read of class clsConfig: " & ex.Message)
            Return False
        End Try

    End Function

End Module
