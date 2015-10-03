# Postgres_slowquery_parser

Input: Postgres slowqwery raw file 

Output: Two Csv file.

File 1: Contains all the queries with the executed time in desending order

Columns: Hash, Group, Time, Query


File 2: Contains all the unique queries with the Max time of that query.


Columns:  Hash, Group, Count, Time, Query

*Hash is basically an uniqueness parameter for the Sql command.

*Group used for grouping the query

*Count is the no of queries found Corresponding to hash

How to Use:

1)Set the $destination_folder where you want to save your files 

2)Set the $files_read array .You can pass no of files in single go.

Thats It.

Now run on shell:
php index.php

Your file will be parsed.
