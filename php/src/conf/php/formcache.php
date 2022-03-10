<?
    /*
     * $Header: /usr/local/cvs/php/formcache.php,v 1.1 2001/09/21 18:39:58 bob Exp $
     *
     *     Class        :    formcache
     *     Version        :    0.2
     *     Author        :    mfischer@josefine.ben.tuwien.ac.at
     *                     http://josefine.ben.tuwien.ac.at/~mfischer/
     *
     *     Description    :    Class which handles cached form submission
     *                    
     *    Exaomple    :    
     *                    If someone just submitted the form and its in the cache,
     *                    serve the user the cached result :
     *
     *                    $fc = new formcache( 'myform', '/tmp/php/');
     *    
     *                    if( $fc->exists( array_merge( $HTTP_POST_VARS, $HTTP_GET_VARS)))
     *                        echo join( '', $fc->serve());
     *                    else
     *                        doGetResult(); // that's up to you
     *
     *
     *                    Somewhere in 'doGetResult()' you then write the output to the cache :
     *    
     *                    $fc->cache( $output);
     */

     // That's up to the end-developer, really
     // error_reporting( E_ALL ^ E_NOTICE);


    /*
        Change this to a default setting which makes sense to your enviroment

        FORMCACHE_NAMESPACE    -    Is part of the name of the cachefile to easier distingiush cached files from different forms

        FORMCACHE_CACHDIR    -    The directory where everything gets writte to and served from
                                Make sure Apache/PHP has the proper rights to write and read files from there
                                If the directory does not exist the class tries to create it automatically
    */
    define( 'FORMCACHE_NAMESPACE', 'formcache');
    define( 'FORMCACHE_CACHEDIR', '/tmp/');


    /**
    * Provides detection if a FORM was submitted a second time a feed
    * feed brwoser with resulting page from cache
    * @access        public
    * @author        Markus Fischer <mfischer@josefine.ben.tuwien.ac.at>
    * @link            http://josefine.ben.tuwien.ac.at Homepage of the author
    * @copyright    Netway AG
    */
    class formcache {

        /**
        * Namespace declaration
        * @var string
        * @access private
        */
        var $namespace;

        /**
        * Directory name where to store/retrieve cached files
        * @var string
        * @access private
        */
        var $cachedir;

        /**
        * Cache filename
        * @var string
        * @access private
        */
        var $cachefile;

        /**
        * Wheter use compression or not when writing to the cache. Defaults to false.
        * @var boolean
        * @access public
        */
        var $compress;

        /**
        * Contains last error message
        * @var string
        * @access public
        */
        var $err;
        
        /**
        * Constructor
        *
        * Instances a new formcheck object.
        *
        * @param string The Namespace the cache lives under
        * @param string Optional directory for reading/writing cached files. Defaults to FORMCACHE_CACHDIR
        * @param boolean Whether use gzip compression (TRUE) or not (FALSE)
        */
        function formcache( $namespace = FORMCACHE_NAMESPACE, $cachedir = FORMCACHE_CACHDIR, $compress = false) {
            $this->namespace    = $namespace;
            if( empty( $this->namespace))
                $this->namespace = FORMCACHE_NAMESPACE;

            $this->cachedir        = $cachedir;
            if( empty( $this->cachedir))
                $this->cachedir = FORMCACHE_CACHEDIR;
            else
                if( $this->cachedir[ strlen( $this->cachedir) - 1] != '/')
                    $this->cachedir .= '/';
                    
            if( ! @is_dir( $this->cachedir)) {
                $dirs = split( '/', $this->cachedir);
                $createdir = '';
                foreach( $dirs as $dir) { $createdir .= '/' . $dir; @mkdir( $createdir, 0775);}
            }

            $this->cachefile    = '';
            if( $compress)
                $this->compress = true;
            else
                $this->compress    = false;
        }

        /**
        * Elicit the disambiguatity for cache-hits by means of the form elements
        *
        * @param array Form elements
        * @return boolean True if uniqie identifier (cachefile) has been generated; False if not (ie. $form was empty or not an array)
        * @access public
        */
        function formdata( $form) {
            $param = array();

            if( empty( $form) || ( ! gettype( $form) == 'array'))
                return $this->error( 'Form elements array is empty or not of type \'array\'');

            // Different browsers return form elements in different orders => fix this!
            ksort( $form, SORT_STRING);

            foreach( $form as $key => $value)
                $param[] = urlencode( $key) . '=' . urlencode( $value);

            $this->cachefile = $this->cachedir . $this->namespace . '-' . md5( join( '&', $param)) . '.cache';

            return true;
        }

        /**
        * Check wether a cached file exists or not
        *
        * @param array A optional hash containing all form elements for determining the disambiguatity.
        *              Use this if you haven't called $this-set() before
        *
        * @return boolean True if a cached file exists; False if not (or there was no cachefile previously generated)
        *
        * @acces public
        */
        function exists( $form = '') {

            $this->formdata( $form);

            if( empty( $this->cachefile))
                return $this->error( 'Cachefile is empty; initialize first with method formadata()');

            if( file_exists( $this->cachefile))
                return true;
            else
                return $this->error( 'Cachefile does not exist');
        }

        /**
        * Serve file from cache
        *
        * @param array  A optional hash containing all form elements for determining the disambiguatity.
        *                Use this if you haven't called $this-set() before
        *
        * @return mixed    Array containing the cached file, line by line. False on failure
        *
        * @access public
        */
        function serve( $form = '') {

            $this->formdata( $form);

            if( empty( $this->cachefile))
                return $this->error( 'Cachefile is empty; initialize first with method formadata()');

            if( $this->exists()) {
                if( function_exists( 'gzfile'))
                    return gzfile( $this->cachefile);
                else
                    return file( $this->cachefile);
            } else
                return false;
        }

        /**
        * Caches the data
        *
        * Note that this function overwrites without warning; use exists() before inserting
        * 
        * @param array    Data to cache
        *
        * @return boolean True on successful cache insertion; False if something has gone wrong
        *
        * @access public
        */
        function cache( $data, $form = '') {

            if( empty( $data) || ( gettype( $data) != 'array'))
                return $this->error( 'Data is empty or not an array');
        
            if( ! empty( $form))
                $this->formdata( $form);

            if( empty( $this->cachefile))
                return $this->error( 'Cachefile is empty; initialize first with method formdata()');

            if( ( $fp = fopen( $this->cachefile, 'wb'))) {
                if( $this->compress) {
                    $compressed = gzencode( join( '', $data));
                    if( strlen( $compressed) != fwrite( $fp, $compressed, strlen( $compressed))) {
                        fclose( $fp);
                        return $this->error( 'Compressed data only partially written; low on disk space ?');
                    }
                } else
                    foreach( $data as $line)
                        if( strlen( $line) != fwrite( $fp, $line, strlen( $line))) {
                            fclose( $fp);
                            return $this->error( 'Data only partially written; low on disk space ?');
                        }
                if( fclose( $fp))
                    return true;
                else
                    $this->error( 'Can\'t close file handle');
            } else
                return $this->error( 'Can\'t open cache file for writing');
        }


        /**
        * Stores error message
        *
        * This method is just for convinience. all it does it stores the parameter in a member variable $this->errmsg and always returns false
        *
        * @param string Optional error message string
        * @return boolean Always returns false
        * @access private
        */
        function error( $message = '') {
            $this->err[] = $message;
            return false;
        }

    }
?>
