
Import Project
======================

Click the Import button at top right to import your Project(s) and sync to PostGIS

Enter your QFieldCloud uername, password, and QFieldCloud url (works with qfield.cloud and self-hosted).  

Click the Load Projects icon


.. image:: _static/quail-qfield-cloud-load-project.png


Select the Project to Sync

.. image:: _static/quail-qfield-cloud-load-project-2.png

Create a new PostGIS database or select an existing PostGIS database


.. image:: _static/quail-qfield-cloud-load-project-3.png

Click the Import button


.. image:: _static/quail-qfield-cloud-load-project-4.png


Start your service(s) and verify it is running

.. image:: _static/quail-qfield-cloud-plugin.png


# script settings
sleep_time=600
single_run='false'
force_init='true'
data_dir="${HOME}/data/survey"
# QField Cloud settings
qf_user='YourQFieldUsername'
qf_pass='YourQFieldPassword'
qf_url='https://app.qfield.cloud/api/v1/'
qf_proj_id='2r56c287-f8cc-470c-92e1-121358t4g8d6'
qf_gpkg='bees'
# database settings
pg_host='localhost'
pg_port='5432'
pg_user='YourDatabaseUser'
pg_pass='YourDatabasePassword'
pg_dbname='YourDatabaseName'
pg_schema='Your_data'



If you need a database, you can create one on the local server

.. code-block:: console

  postgres=# create user beeuser with password 'SuperSecret';
  CREATE ROLE
  postgres=# create database beedb with owner beeuser;
  CREATE DATABASE
  postgres=# \c beedb
  You are now connected to database "beedb" as user "postgres".
  beedb=# CREATE EXTENSION postgis;
  CREATE EXTENSION
  beedb=#


Check your datbase to see it is pulling data from your project


.. code-block:: sql

  postgres=# \c beedb
  beedb=# \dn
         List of schemas
     Name   |       Owner
  ----------+-------------------
   bee_data | beeuser
   public   | pg_database_owner
  (2 rows)

  beedb=# \dt bee_data.*
                  List of relations
    Schema  |        Name        | Type  |  Owner
  ----------+--------------------+-------+---------
   bee_data | Pollen_Consumption | table | beeuser
   bee_data | Reviews            | table | beeuser
   bee_data | apiary             | table | beeuser
   bee_data | area               | table | beeuser
  (4 rows)

  beedb=# select bee_species from bee_data.apiary LIMIT 5;
       bee_species
  --------------------------
   Apis Mellifera
   Apis Mellifera
   Apis Mellifera Mellifera
   Apis Mellifera Carnica
   Apis Mellifera Carnica
  (5 rows)




Based on Mergin Maps db-sync, qfield-db-sync provides synchronization between QFieldCloud and PostGIS

qfield-db-sync works with qfield.cloud as well as self hosted qfieldcloud instances.






