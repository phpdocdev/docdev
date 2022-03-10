<?php
/****************************************************************
* Script         : Database functions for PhpSimpleXlsGen Class
* Project        : PHP SimpleXlsGen
* Author         : Erol Ozcan <eozcan@superonline.com>
* Version        : 0.1
* Copyright      : GNU LGPL
* URL            : http://psxlsgen.sourceforge.net
* Last modified  : 17 May 2001
******************************************************************/
if( !defined( "DB_SIMPLE_XLS_GEN" ) ) {
   define( "DB_SIMPLE_XLS_GEN", 1 );

   Class Db_SXlsGen extends PhpSimpleXlsGen {
      var $db_host     = "localhost";
      var $db_user     = "mysql";
      var $db_passwd   = "";
      var $db_name     = "mysql";
      var $db_type     = "mysql";
      var $db_con_id   = "";
      var $db_query    = "";
      var $db_stmt     = "";
      var $db_ncols    = 0;
      var $db_nrows    = 0;
      var $db_fetchrow = array();
      var $col_aliases = array();

      // default constructor
      function CDb_SXlsGen()
      {
         $this->PhpSimpleXlsGen();
      }

      function InsertColNames( $cmd_colname )
      {
         $this->totalcol = $this->db_ncols;
         for( $i = 0; $i < $this->db_ncols; $i++ ) {
            // variable function is used
            $col = $cmd_colname( $this->db_stmt, $i );
            if ( $this->col_aliases["$col"] != "" ) {
               $colname = $this->col_aliases[$col];
            } else {
               $colname = $col;
            }
            $this->InsertText( $colname );
         }
      }

      function InsertRows( $cmd_rowfetch )
      {
         $row = array();
         for( $i = 0; $i < $this->db_nrows; $i++ ) {
           if ( $this->db_type == "pgsql" ) {
              $row = $cmd_rowfetch( $this->db_stmt, $i );
           } else {
              $row = $cmd_rowfetch( $this->db_stmt );
           }
           for ( $j = 0; $j < $this->db_ncols; $j++ ) {
              $this->InsertText( $row[$j] );
           }
         }
      }

      function FetchData()
      {
         switch ( $this->db_type ) {
            case "mysql":
                  $this->db_con_id = mysql_connect( $this->db_host, $this->db_user, $this->db_passwd );
                  $this->db_stmt = mysql_db_query( $this->db_name, $this->db_query, $this->db_con_id );
                  $this->db_ncols = mysql_num_fields( $this->db_stmt );
                  $this->InsertColNames( "mysql_field_name" );
                  $this->db_nrows = mysql_num_rows( $this->db_stmt );
                  $this->InsertRows( "mysql_fetch_array" );
                  mysql_free_result ( $this->db_stmt );
                  mysql_close( $this->db_con_id );
                  break;

            case "pgsql":
                  $this->db_con_id = pg_connect( "host=".$this->db_host." dbname=".$this->db_name." user=".$this->db_user." password=".$this->db_passwd );
                  $this->db_stmt = pg_exec( $this->db_con_id, $this->db_query );
                  $this->db_ncols = pg_numfields( $this->db_stmt );
                  $this->InsertColNames( "pg_fieldname" );
                  $this->db_nrows = pg_numrows( $this->db_stmt );
                  $this->InsertRows( "pg_fetch_row" );
                  pg_freeresult( $this->db_stmt );
                  pg_close( $this->db_con_id );
                  break;

            case "oci8":
                  $this->db_con_id = OCILogon( $this->db_user, $this->db_passwd, $this->db_name );
                  $this->db_stmt = OCIParse( $this->db_con_id, $this->db_query );
                  OCIExecute( $this->db_stmt );
                  $this->db_ncols = OCINumCols( $this->db_stmt );
                  // fetching column names and rows are differents in OCI8.
                  $tmparr = array();
                  $this->db_nrows = OCIFetchStatement( $this->stmt, &$results );
                  $this->totalcol = $this->db_ncols;
                  while ( list($key, $val ) = each( $results ) ) {
                     if ( $this->col_aliases[$key] != "" ) {
                       $colname = $this->col_aliases[$key];
                     } else {
                       $colname = $key;
                     }
                     $this->InserText( $colname );
                  }
                  for ( $i = 0; $i < $nrows; $i++ ) {
                     reset( $results );
                     while ( $column = each( $results ) ) {
                        $data = $column['value'];
                        $this->InsertText( $data[$i] );
                     }
                  }
                  OCIFreeStatement( $this->db_stmt );
                  OCILogoff( $this->db_con_id );
                  break;

            default:

                  break;
         }
      }

      function GetXlsFromQuery( $query )
      {
           $this->db_query = $query;
           $this->FetchData();
           $this->GetXls();
      }

   } // end of class CDb_SXlsGen
}
// end of ifdef DB_SIMPLE_XLS_GEN