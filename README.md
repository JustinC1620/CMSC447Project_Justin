# CMSC447Project
## Description
The UMBC ASC currently faces a problem with drop-in tutoring where students may come in during a time slot listed on the current ASC drop-in tutoring website but find the tutor absent due to late arrivals, leaving early, or call-out. The current drop-in tutoring schedule website displays a static schedule of tutoring slots by class, tutor, and time but does not have the capability for dynamic updates for daily schedule adjustments. While the ASC also uses TracCloud software to check in tutors and send alerts for canceled sessions, this information is not reflected on the public-facing UMBC page and TracCloud is unreliable. The ASC Dynamic Drop-In Tutoring Website is intended to be an improved version of the current UMBC website that displays available tutoring slots along with real-time statuses and alerts for specific tutors and times, updated automatically or directly by ASC staff. These improvements serve to keep students informed of schedule changes in real time so they can adjust their plans accordingly, minimize unnecessary visits to the ASC when tutoring is unavailable, and reduce the burden on front desk staff from redirecting students whose tutor is absent.

## Installation 
Download XAMPP. https://www.apachefriends.org/ <br>
Install XAMPP. During "Select Components" disselect the following:  
-    FileZilla FTP Server  
-    Mercury Maill Server  
-    Tomcat  
-    Webalizer  
-    Fake Sendmail

Replace the contents of the htdocs folder of the XAMPP install with the files in htdocs from the repo.  
Start XAMPP control panel and start the Apache Server and MySQL (MariaDB) servers.  
Click "Admin" for MySQL, this should open a webpage at https://localhost/phpmyadmin.  
Select "Databases" at the the top of the page.  
Create databases with names "asc_website_db" and "umbc_db".  
Select "asc_website_db" the "Import" at the top of the page.  
Chose the "asc_website_db.sql" file from the database folder from the repo and click "Import" and the bottom of the page.  
Select "umbc_db" the "Import" at the top of the page.  
Chose the "umbc_db.sql" file from the database folder from the repo and click "Import" and the bottom of the page.  
Click "User accounts" at the top of the page then "Add user account" fro the center of the page.  
Fill out the account fields as follows:  
-    User name: wordpress  
-    Host name: 127.0.0.1  
-    Password & Re-type: wordpress

Click "Go" at the botom of the page.  
Select "Database" near the top of the page (note: NOT "Databases").  
Select "asc_website_db" then click "Go".  
Select "Check all" then click "Go"   
Install and run Redis:
> Linux  
> `sudo apt update`  
> `sudo apt install -y redis-server`  
> `sudo systemctl enable redis`

> Mac  
> `brew install redis`  
> `brew services start redis`

> Windows  
> Download, Install, and run Docker. https://www.docker.com/  
> `docker pull redis`  
> `docker run --name my-redis -d -p 6379:6379 redis` (after restart must be run again or started through the docker gui)  

Open a webpage at https://localhost/drop-in-tutoring.
