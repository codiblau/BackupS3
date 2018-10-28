# BackupS3
Upload folders, files and database MySQL (or MariaDB) to S3

#### 1. Setup

1- Rename properties-sample.php to properties.php

2- Set up properties.php with your parameters

That's all!

#### 2. More information:

You should call file "backups3.php" every time you want a backup to your Bucket. I recommend you create a daily **cron** 
task in your server.

Monday to Saturday, only database is backed up.

Sunday the script create a back up of your databases AND files also. This files are splitted in 5GB data and uploaded
 to your Bucket. You can change this behaviour removing this line: 
        `//0 is SUNDAY
        if ($weekDay == 0) {`

