.. This is a comment. Note how any initial comments are moved by
   transforms to after the document title, subtitle, and docinfo.

.. demo.rst from: http://docutils.sourceforge.net/docs/user/rst/demo.txt

.. |EXAMPLE| image:: static/yi_jing_01_chien.jpg
   :width: 1em

**********************
Create Database
**********************
.. contents:: Table of Contents

Overview
==================

You can create a new empty PostGIS database or create a PostGIS database from a data file or backup.






Create Empty Database
======================

To create an empty PostGIS database, click the Create button

  .. image:: _static/db-create.png

Give your database a name and check the "Database Only" check box.

  .. image:: _static/db-create-db-only.png

The PostGIS database has now been created.

  .. image:: _static/db-empty-created.png


Connection Information
======================

To view the Connection information for a PostGIS database, click the Connection icon at right.

  .. image:: _static/db-show-conn-1.png

The conneciton information is displayed in Modal format

  .. image:: _static/db-show-conn-2.png


1. Create
------------------------

Right click on layer > Export > Save As > GeoPackage

  .. image:: images/create-db-1.png



2. Upload GeoPackages
-------------------------

Go to Data Sources > Create and upload your GeoPackage(s).

  .. image:: images/create-db-2.png


3. Data Source is Created
-------------------------------

Set your map layer(s) to use your new Data Source

 .. image:: images/qwc_conn_info_0.png

 
4. Connection Information
-------------------------------

You can retrieve the database connection information at any time by clicking the Connection link.


 .. image:: images/qwc_conn_info.png

