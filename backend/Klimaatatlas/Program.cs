using System;
using System.Configuration;
using System.Data;
using System.IO;
using System.Text.Json;
using System.Threading.Tasks;
using Dapper;
using MathNet.Symbolics;
using Microsoft.Data.Sqlite;
using Microsoft.Extensions.Configuration;

namespace Klimaatatlas
{
    class Program
    {
        static async Task Main(string[] args)
        {
            IConfiguration config = new ConfigurationBuilder()
                .SetBasePath(Directory.GetCurrentDirectory())
                .AddJsonFile("appsettings.json", optional: false, reloadOnChange: true)
                .Build();

            var jsonString = File.ReadAllText("input.json");
            var input = JsonSerializer.Deserialize<Input>(jsonString);

            using var connection = new SqliteConnection(config.GetConnectionString("KlimaatatlasDb"));

            foreach (var rule in input.Rules)
            {
                switch (rule.OperationType)
                {
                    case "timeseries_transformation":
                        await TimeseriesTransformationAsync(connection, rule, input);
                        break;
                    case "timeseries_filter":
                        await TimeseriesFilterAsync(connection, rule, input);
                        break;
                    case "timeseries_classification":
                        await TimeseriesClassificationAsync(connection, rule, input);
                        break;
                    default:
                        Console.WriteLine($"Unknown operation type: {rule.OperationType}");
                        break;
                }
            }

            Console.WriteLine("All rules have been processed.");
        }

        private static async Task TimeseriesTransformationAsync(SqliteConnection connection, Rule rule, Input input)
        {
            // ... Implement the timeseries_transformation logic ...
        }

        private static async Task TimeseriesFilterAsync(SqliteConnection connection, Rule rule, Input input)
        {
            // ... Implement the timeseries_filter logic ...
        }

        private static async Task TimeseriesClassificationAsync(SqliteConnection connection, Rule rule, Input input)
        {
            // ... Implement the timeseries_classification logic ...
        }
    }

    public class Input
    {
        public List<Dataset> Datasets { get; set; }
        public List<Rule> Rules { get; set; }
    }

    public class Dataset
    {
        public string Id { get; set; }
        public string DataType { get; set; }
        public string StorageType { get; set; }
        public string Path { get; set; }
        public string TableName { get; set; }
        public List<Field> Fields { get; set; }
    }

    public class Field
    {
        public string FieldType { get; set; }
        public string FieldName { get; set; }
        public List<string> Selection { get; set; }
    }

    public class Rule
    {
        public string Name { get; set; }
        public string OperationType { get; set; }
        public InputOutput Input { get; set; }
        public string Equation { get; set; }
        public Filter Filter { get; set; }
        public InputOutput Output { get; set; }
    }

    public class InputOutput
    {
        public string Dataset { get; set; }
        public string ParameterName { get; set; }
    }

    public class Filter
    {
        public string Type { get; set; }
        public List<int> Args { get; set; }
        public int ValueTrue { get; set; }
        public int ValueFalse { get; set; }
        public int Value { get; set; }
        public List<Class> Classes { get; set; }
    }

    public class Class
    {
        public int From { get; set; }
        public int To { get; set; }
        public int Rating { get; set; }
    }


}
