<?php
/*********************************************************************************/
/**
 * iCalcreator class v2.0
 * copyright (c) 2007 Kjell-Inge Gustafsson
 * www.kigkonsult.se/iCalcreator/index.php
 * ical@kigkonsult.se
 *
 * Description:
 * This file is a PHP implementation of RFC 2445.
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */
/*********************************************************************************/
/*********************************************************************************/
/*         A little setup                                                        */
/*********************************************************************************/
            /* your local language code */
// define( 'ICAL_LANG', 'sv' );
            // alt. autosetting
/*
$langstr     = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
$pos         = strpos( $langstr, ';' );
if ($pos   !== false) {
  $langstr   = substr( $langstr, 0, $pos );
  $pos       = strpos( $langstr, ',' );
  if ($pos !== false) {
    $pos     = strpos( $langstr, ',' );
    $langstr = substr( $langstr, 0, $pos );
  }
  define( 'ICAL_LANG', $langstr );
}
*/
            /* only for phpversion 5.x, date management, default timezone setting */
if( substr( phpversion(), 0, 1) >= '5' ) {
  date_default_timezone_set( 'Europe/Stockholm' );
}
            /* version string, do NOT remove!! */
define( 'ICALCREATOR_VERSION', 'iCalcreator 2.0' );
/*********************************************************************************/
/*********************************************************************************/
/**
 * vcalendar class
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.8 - 2006-11-27
 */
class vcalendar {
            //  calendar property variables
  var $calscale;
  var $method;
  var $prodid;
  var $version;
  var $xprop;
            //  container for calendar components
  var $components;
            //  component config variables
  var $unique_id;
  var $language;
  var $directory;
  var $filename;
  var $delimiter;
  var $nl;
  var $format;
            //  component internal variables
  var $attributeDelimiter;
  var $valueInit;
            //  component xCal declaration container
  var $xcaldecl;
/*
 * constructor for calendar object
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.22 - 2007-04-08
 * @return void
 */
  function vcalendar () {
    $this->_makeVersion();
    $this->calscale   = null;
    $this->method     = null;
    $this->_makeUnique_id();
    $this->prodid     = null;
    $this->xprop      = array();
/**
 *   language = <Text identifying a language, as defined in [RFC 1766]>
 */
    if( defined( 'ICAL_LANG' ))
      $this->setConfig( 'language', ICAL_LANG );
    $this->setConfig( 'nl',         "\n" );
    $this->setConfig( 'format',     'iCal');
    $this->directory  = null;
    $this->filename   = null;
    $this->setConfig( 'delimiter',  DIRECTORY_SEPARATOR );
    $this->xcaldecl   = array();
    $this->components = array();
  }
/*********************************************************************************/
/**
 * Property Name: CALSCALE
 */
/**
 * creates formatted output for calendar property calscale
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.7 - 2006-11-20
 * @return string
 */
  function createCalscale() {
    if( !isset( $this->calscale ))
      return;
    switch( $this->format ) {
      case 'xcal':
        return ' calscale="'.$this->calscale.'"'.$this->nl;
        break;
      default:
        return 'CALSCALE:'.$this->calscale.$this->nl;
        break;
    }
  }
/**
 * set calendar property calscale
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-13
 * @param string $value
 * @return void
 */
  function setCalscale( $value ) {
    $this->calscale = $value;
  }
/*********************************************************************************/
/**
 * Property Name: METHOD
 */
/**
 * creates formatted output for calendar property method
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.7 - 2006-11-20
 * @return string
 */
  function createMethod() {
    if( !isset( $this->method ))
      return;
    switch( $this->format ) {
      case 'xcal':
        return ' method="'.$this->method.'"'.$this->nl;
        break;
      default:
        return 'METHOD:'.$this->method.$this->nl;
        break;
    }
  }
/**
 * set calendar property method
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-13
 * @param string $method
 * @return void
 */
  function setMethod( $method ) {
    $this->method = $method;
  }
/*********************************************************************************/
/**
 * Property Name: PRODID
 *
 *  The identifier is RECOMMENDED to be the identical syntax to the
 * [RFC 822] addr-spec. A good method to assure uniqueness is to put the
 * domain name or a domain literal IP address of the host on which.. .
 */
/**
 * creates formatted output for calendar property prodid
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.7 - 2006-11-20
 * @return string
 */
  function createProdid() {
    if( !isset( $this->prodid ))
      $this->_makeProdid();
    switch( $this->format ) {
      case 'xcal':
        return ' prodid="'.$this->prodid.'"'.$this->nl;
        break;
      default:
        return 'PRODID:'.$this->prodid.$this->nl;
        break;
    }
  }
/**
 * make default value for calendar prodid
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @return void
 */
  function _makeProdid() {
    $this->prodid  = '-//'.$this->unique_id.'//NONSGML '.ICALCREATOR_VERSION.'//'.strtoupper( $this->language );
  }
/**
 * Conformance: The property MUST be specified once in an iCalendar object.
 * Description: The vendor of the implementation SHOULD assure that this
 * is a globally unique identifier; using some technique such as an FPI
 * value, as defined in [ISO 9070].
 */
/**
 * make default unique_id for calendar prodid
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @return void

 */
  function _makeUnique_id() {
    $this->unique_id  = gethostbyname( $_SERVER['SERVER_NAME'] );
  }
/*********************************************************************************/
/**
 * Property Name: VERSION
 *
 * Description: A value of "2.0" corresponds to this memo.
 */
/**
 * creates formatted output for calendar property version

 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.7 - 2006-11-20
 * @return string
 */
  function createVersion() {
    if( !isset( $this->version ))
      $this->_makeVersion();
    switch( $this->format ) {
      case 'xcal':
        return ' version="'.$this->version.'"'.$this->nl;
        break;
      default:
        return 'VERSION:'.$this->version.$this->nl;
        break;
    }
  }
/**
 * set default calendar version
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @return void
 */
  function _makeVersion() {
    $this->version = '2.0';
  }
/**
 * set calendar version
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @param string version
 * @return void
 */
  function setVersion( $version ) {
    $this->version = $version;
  }
/*********************************************************************************/
/**
 * Property Name: x-prop
 */
/**
 * creates formatted output for calendar property x-prop, iCal format only
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.22 - 2007-04-07
 * @return string
 */
  function createXprop() {
    if( 'xcal' == $this->format )
      return false;
    if( 0 >= count( $this->xprop ))
      return;
    $xprop = null;
    $toolbox = new calendarComponent();
    $toolbox->setConfig( 'language', $this->getConfig( 'language' ));
    $toolbox->setConfig( 'nl',       $this->getConfig( 'nl' ));
    $toolbox->_createFormat(         $this->getConfig( 'format' ));
    foreach( $this->xprop as $xpropPart ) {
      $attributes = $toolbox->_createParams( $xpropPart['params'], array( 'LANGUAGE' ));
      foreach( $xpropPart['value'] as $label => $value ) {
        if( is_array( $value ))
          $value  = implode( ',', $value );
        $xprop   .= $toolbox->_createElement( strtoupper( $label ), $attributes, $value );
      }
    }
    return $xprop;
  }
/**
 * set calendar property x-prop
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 1.x.x - 2007-05-07
 * @param string $label
 * @param string $value
 * @param array $params optional
 * @return void
 */
  function setXprop( $label, $value, $params=FALSE ) {
    $xprop['value'] = array( $label => $value);
    if( empty( $xprop['value'] ))
      return;
    $toolbox = new calendarComponent();
    $xprop['params'] = $toolbox->_setParams( $params );
    $this->xprop[] = $xprop;
  }
/*********************************************************************************/
/**
 * get calendar property value/params
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 1.x.x - 2007-05-15
 * @param string $propName, optional
 * @param int @propix, optional, if specific property is wanted in case of multiply occurences
 * @param bool $inclParam=FALSE
 * @return mixed
 */
  function getProperty( $propName=FALSE, $propix=FALSE, $inclParam=FALSE ) {
    $propName = ( $propName ) ? strtoupper( $propName ) : 'X-PROP';
    if( !$propix )
      $propix = ( isset( $this->propix[$propName] )) ? $this->propix[$propName] + 2 : 1;
    $this->propix[$propName] = --$propix;
    switch( $propName ) {
      case 'CALSCALE':
        if( 0 < $propix ) return FALSE;
        return ( !empty( $this->calscale )) ? $this->calscale : null;
        break;
      case 'METHOD':
        if( 0 < $propix ) return FALSE;
        return ( !empty( $this->method )) ? $this->version : null;
        break;
      case 'PRODID':
        if( 0 < $propix ) return FALSE;
        if( empty( $this->prodid ))
          $this->_makeProdid();
        return $this->prodid;
        break;
      case 'VERSION':
        if( 0 < $propix ) return FALSE;
        return ( !empty( $this->version )) ? $this->version : null;
        break;
      default:
        if( count( $this->xprop ) <= $propix ) return null;
        return ( $inclParam ) ? $this->xprop[$propix] : $this->xprop[$propix]['value'];
        break;
    }
    return FALSE;
  }
/**
 * general vcalendar property setting
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.21 - 2007-04-15
 * @param mixed $args variable number of function arguments,
 *                    first argument is ALWAYS component name!
 * @return void
 */
  function setProperty () {
    $numargs    = func_num_args();
    if( 1 >= $numargs )
      return FALSE;
    $arglist    = func_get_args();
    $arglist[0] = strtoupper( $arglist[0] );
    for( $argix=$numargs; $argix < 3; $argix++ )
      if( !isset( $arglist[$argix] ))
        $arglist[$argix] = null;
    switch( $arglist[0] ) {
      case 'CALSCALE':
        $this->setCalscale( $arglist[1] );
        break;
      case 'METHOD':
        $this->setMethod( $arglist[1] );
        break;
      case 'VERSION':
        $this->setVersion( $arglist[1] );
        break;
      default:
        $this->setXprop( $arglist[0], $arglist[1], $arglist[2] );
        break;
    }
  }
/*********************************************************************************/
/**
 * get vcalendar config values or * calendar components
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 1.x.x - 2007-05-22
 * @param string $config
 * @return value
 */
  function getConfig( $config ) {
    switch( strtoupper( $config )) {
      case 'COMPSINFO':
        unset( $this->compix );
        $info = array();
        foreach( $this->components as $cix => $component ) {
          unset( $component->propix );
          $info[$cix]['ordno'] = $cix + 1;
          $info[$cix]['type']  = $component->objName;
          $info[$cix]['uid']   = $component->getProperty( 'uid' );
          $info[$cix]['props'] = $component->getConfig( 'propinfo' );
          $info[$cix]['sub']   = $component->getConfig( 'compsinfo' );
        }
        return $info;
        break;
      case 'DELIMITER':
        return $this->delimiter;
        break;
      case 'DIRECTORY':
        if( empty( $this->directory ))
          $this->directory = '.';
        return $this->directory;
        break;
      case 'DIRFILE':
        return $this->getConfig( 'directory' ).$this->getConfig( 'delimiter' ).$this->getConfig( 'filename' );
        break;
      case 'FILEINFO':
        return array( $this->getConfig( 'directory' )
                    , $this->getConfig( 'filename' )
                    , $this->getConfig( 'filesize' ));
        break;
      case 'FILENAME':
        if( empty( $this->filename )) {
          if( 'xcal' == $this->format )
            $this->filename = date( 'YmdHis' ).'.xml'; // recommended xcs.. .
          else
            $this->filename = date( 'YmdHis' ).'.ics';
        }
        return $this->filename;
        break;
      case 'FILESIZE':
        return filesize( $this->getConfig( 'dirfile' ));
        break;
      case 'FORMAT':
        return $this->format;
        break;
      case 'LANGUAGE':
         /* get language for calendar component as defined in [RFC 1766] */
        return $this->language;
        break;
      case 'NL':
      case 'NEWLINECHAR':
        return $this->nl;
        break;
      case 'UNIQUE_ID':
        return $this->unique_id;
        break;
    }
  }
/**
 * general vcalendar config setting
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 1.x.x - 2007-05-17
 * @param string $config
 * @param string $value
 * @return void
 */
  function setConfig( $config, $value ) {
    switch( strtoupper( $config )) {
      case 'DELIMITER':
        $this->delimiter = $value;
        break;
      case 'DIRECTORY':
        if( @is_writable( $value )) {
          $this->directory = $value;
          return TRUE;
        }
        else
          return FALSE;
        break;
      case 'FILENAME':
        if( file_exists($this->getConfig('directory').$this->getConfig('delimiter').$value )) {
          if( is_readable($this->getConfig('directory').$this->getConfig('delimiter').$value ) &&
              is_writable($this->getConfig('directory').$this->getConfig('delimiter').$value )) {
            $this->filename = $value;
            return TRUE;
          }
          else
            return FALSE;
        }
        elseif( @touch( $this->getConfig('directory').$this->getConfig('delimiter').$value )) {
          $this->filename = $value;
          return TRUE;
        }
        else
          return FALSE;
        break;
      case 'FORMAT':
        if( 'xcal' == strtolower( $value )) {
          $this->format             = 'xcal';
          $this->attributeDelimiter = $this->nl;
          $this->valueInit          = null;
        }
        else {
          $this->format             = null;
          $this->attributeDelimiter = ';';
          $this->valueInit          = ':';
        }
        break;
      case 'LANGUAGE':
         // set language for calendar component as defined in [RFC 1766]
        $this->language = $value;
        break;
      case 'NL':
      case 'NEWLINECHAR':
        $this->nl = $value;
        break;
      case 'UNIQUE_ID':
        $this->unique_id = $value;
        break;
    }
  }
/*********************************************************************************/
/**
 * validDate
 *
 * convert input parameters to (valid) iCalcreator date in array format (or FALSE)
 * if $utc=TRUE and $tz = utc offset ([[+/]-]HHmm) input (local) date array + UTC offset
 * returns ouput in UTC format date
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 1.x.x - 2007-05-12
 * @param mixed $year
 * @param mixed $month optional
 * @param int $day optional
 * @param int $hour optional
 * @param int $min optional
 * @param int $sec optional
 * @param mixed $tz optional
 * @param bool $utc optional
 * @return bool false / array $date
 */
  function validDate( $year, $month=FALSE, $day=FALSE, $hour=FALSE, $min=FALSE, $sec=FALSE, $tz=FALSE, $utc=FALSE ) {
    $input = array();
    $toolbox = new calendarComponent();
    $parno = null;
    if( is_int( $year ) && is_int( $month ) && is_int( $day )) {
      $input['year']   = $year;
      $input['month']  = $month;
      $input['day']    = $day;
      if(( $hour !== FALSE ) || ( $min !== FALSE ) || ( $sec !== FALSE )) {
        $parno = 6;
        if( $hour !== FALSE )
          $input['hour'] = $hour;
        if( $min !== FALSE )
          $input['min']  = $min;
        if( $sec !== FALSE )
          $input['sec']  = $sec;
      }
      if( $tz !== FALSE ) {
        $parno = 7;
        $input['tz']  = $tz;
      }
      elseif( !$parno )
        $parno = 3;
      $input = $toolbox->_date_time_array( $input, $parno );
    }
    elseif( is_array( $year ) && isset( $year['timestamp'] )) {
      $input        = $toolbox->_date_time_string( date( 'Y-m-d H:i:s', $year['timestamp'] ), 6 );
      $input['tz']  = ( isset( $year['tz'] )) ? $year['tz'] : null;
      $utc = ( TRUE === $month ) ? TRUE : FALSE;
    }
    elseif( is_array( $year ) && ( in_array( count( $year ), array( 3, 4, 6, 7 )))) {
      if( isset( $year['tz'] ) || ( 4 == count( $year )) || ( 7 == count( $year )))
        $parno = 7;
      elseif( isset( $year['hour'] ) || isset( $year['min'] ) || isset( $year['sec'] ) ||
            ( 6 == count( $year )))
        $parno = 6;
      else
        $parno = 3;
      $input = $toolbox->_date_time_array( $year, $parno );
      $utc = ( TRUE === $month ) ? TRUE : FALSE;
    }
    elseif( 8 <= strlen( trim( $year ))) { // ex. 2006-08-03 10:12:18
      $input = $toolbox->_date_time_string( $year );
      $utc = ( TRUE === $month ) ? TRUE : FALSE;
    }
    else
      return FALSE;
    if( !checkdate ( $input['month'], $input['day'], $input['year'] ))
      return FALSE;
    if( isset( $input['hour'] ) &&
        (( 0 > $input['hour'] ) || ( 23 < $input['hour'] )))
      return FALSE;
    if( isset( $input['min'] ) &&
        (( 0 > $input['min'] ) || ( 59 < $input['min'] )))
      return FALSE;
    if( isset( $input['sec'] ) &&
        (( 0 > $input['sec'] ) || ( 59 < $input['sec'] )))
      return FALSE;
    if( isset( $input['tz'] ) && ( '' < trim ( $input['tz'] ))) {
      $input['tz'] = trim( $input['tz'] );
      if( ctype_digit( $input['tz']{1} )) { // only numeric tz=offset
        $offset = 0;
        if( ctype_digit( $input['tz']{0} ))
          $input['tz'] = '+'.$input['tz'];
        if((      5 == strlen( $input['tz'] )) &&
           ( '0000' <= substr( $input['tz'], -4 )) &&
           ( '9999' >= substr( $input['tz'], -4 )) &&
             (( '+' == substr( $input['tz'], 0, 1 )) ||
              ( '-' == substr( $input['tz'], 0, 1 )))) {
          $hours2sec = substr( $input['tz'], 1, 2 ) * 3600;
          $min2sec   = substr( $input['tz'], -2 )   *   60;
          $sign      = substr( $input['tz'], 0, 1 );
          $offset    = $sign.'1' * ( $hours2sec + $min2sec );
        }
        elseif(( 7  == strlen( $input['tz'] )) &&
         ( '000000' <= substr( $input['tz'], -6 )) &&
         ( '999999' >= substr( $input['tz'], -6 )) &&
             (( '+' == substr( $input['tz'], 0, 1 )) ||
              ( '-' == substr( $input['tz'], 0, 1 )))) {
          $hours2sec = substr( $input['tz'], 1, 2 ) * 3600;
          $min2sec   = substr( $input['tz'], 3, 2 ) *   60;
          $sec       = substr( $input['tz'], -2 );
          $sign      = substr( $input['tz'], 0, 1 );
          $offset    = $sign.'1' * ( $hours2sec + $min2sec + $sec );
        }
        if( 0 != $offset) {
          if( !isset( $input['hour'] ))
            $input['hour'] = 0;
          if( !isset( $input['min'] ))
            $input['min'] = 0;
          if( !isset( $input['sec'] ))
            $input['sec'] = 0;
          $input = date('Y-m-d H:i:s\Z', mktime ( $input['hour']
                                                , $input['min']
                                                , $input['sec'] + $offset
                                                , $input['month']
                                                , $input['day']
                                                , $input['year']));
          $parno = ( $utc ) ? 7 : 6 ;
          $input = $toolbox->_date_time_string( $input, $parno );
          if( !$utc && isset( $input['tz'] ) && ( 'Z' == $input['tz'] ))
            unset( $input['tz'] );
        }
      }
    }
    return $input;
  }
/*********************************************************************************/
/**
 * add calendar component to container
 *
 * alias to setComponent
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 1.x.x - 2007-04-24
 * @param object $component calendar component
 * @return void
 */
  function addComponent( $component ) {
    $this->setComponent( $component );
  }
/**
 * delete calendar component from container
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 1.x.x - 2007-05-06
 * @param mixed $arg1 ordno / component type / component uid
 * @param mixed $arg2 optional, ordno if arg1 = component type
 * @return void
 */
  function deleteComponent( $arg1, $arg2=FALSE  ) {
    $argType = $index = null;
    if ( is_int( $arg1 )) {
      $argType = 'INDEX';
      $index = --$arg1;
    }
    elseif(( strlen( $arg1 ) <= strlen( 'vfreebusy' )) && ( FALSE === strpos( $arg1, '@' ))) {
      $argType = strtolower( $arg1 );
      $index = ( is_int( $arg2 )) ? --$arg2 : 0;
    }
    $cix1dC = 0;
    foreach ( $this->components as $cix => $component) {
      unset( $component->propix );
      if(( 'INDEX' == $argType ) && ( $index == $cix )) {
        unset( $this->components[$cix] );
        return TRUE;
      }
      elseif( $argType == $component->objName ) {
        if( $index == $cix1dC ) {
          unset( $this->components[$cix] );
          return TRUE;
        }
        $cix1dC++;
      }
      elseif( !$argType && ($arg1 == $component->getProperty( 'uid' ))) {
        unset( $this->components[$cix] );
        return TRUE;
      }
    }
    return FALSE;
  }
/**
 * get calendar component from container
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 1.x.x - 2007-05-06
 * @param mixed $arg1 optional, ordno/component type/ component uid
 * @param mixed $arg2 optional, ordno if arg1 = component type
 * @return object
 */
  function getComponent( $arg1=FALSE, $arg2=FALSE ) {
    $index = $argType = null;
    if ( !$arg1 ) {
      $argType = 'INDEX';
      $index = $this->compix['INDEX'] =
        ( isset( $this->compix[$argType] )) ? $this->compix[$argType] + 1 : 1;
      $index = $index - 1;
    }
    elseif ( is_int( $arg1 )) {
      $argType = 'INDEX';
      $index = $arg1 - 1;
      unset( $this->compix );
    }
    elseif(( strlen( $arg1 ) <= strlen( 'vfreebusy' )) && ( FALSE === strpos( $arg1, '@' ))) {
      unset( $this->compix['INDEX'] );
      $argType = strtolower( $arg1 );
      if( !$arg2 )
        $index = $this->compix[$argType] =
        ( isset( $this->compix[$argType] )) ? $this->compix[$argType] + 1 : 1;
      else
        $index = $arg2;
      $index = $index - 1;
    }
    if( isset( $index) && ( $index > end( array_keys( $this->components ))))
      return FALSE;
    $cix1gC = 0;
    foreach ( $this->components as $cix => $component) {
      unset( $component->propix );
      if(( 'INDEX' == $argType ) && ( $index == $cix ))
        return $component;
      elseif( $argType == $component->objName ) {
         if( $index == $cix1gC )
           return $component;
         $cix1gC++;
      }
      elseif( !$argType && ($arg1 == $component->getProperty( 'uid' ))) {
        unset( $component->propix );
        return $component;
      }
    }
            /* not found.. . */
    unset( $this->compix );
    return false;
  }
/**
 * add calendar component to container
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 1.x.x - 2007-04-24
 * @param object $component calendar component
 * @param mixed $arg1 optional, ordno/component type/ component uid
 * @param mixed $arg2 optional, ordno if arg1 = component type
 * @return void
 */
  function setComponent( $component, $arg1=FALSE, $arg2=FALSE  ) {
    if( '' >= $component->getConfig( 'language'))
      $component->setConfig( 'language' ,  $this->getConfig( 'language' ));
    $component->setConfig( 'nl' ,          $this->getConfig( 'nl' ));
    $component->setConfig( 'unique_id' ,   $this->getConfig( 'unique_id' ));
    $component->setConfig( 'format' ,      $this->getConfig( 'format' ));
    if( !in_array( $component->objName, array( 'valarm', 'vtimezone' ))) {
      unset( $component->propix );
            /* make sure dtstamp and uid is set */
      $dummy = $component->getProperty( 'dtstamp' );
      $dummy = $component->getProperty( 'uid' );
    }
    if( !$arg1 ) {
      $this->components[] = $component;
      return TRUE;
    }
    $argType = $index = null;
    if ( is_int( $arg1 )) {
      $argType = 'INDEX';
      $index = --$arg1;
    }
    elseif(( strlen( $arg1 ) <= strlen( 'vfreebusy' )) && ( FALSE === strpos( $arg1, '@' ))) {
      $argType = strtolower( $arg1 );
      $index = ( is_int( $arg2 )) ? --$arg2 : 0;
    }
    $cix1sC = 0;
    foreach ( $this->components as $cix => $component2) {
      unset( $component2->propix );
      if(( 'INDEX' == $argType ) && ( $index == $cix )) {
        $this->components[$cix] = $component;
        return TRUE;
      }
      elseif( $argType == $component2->objName ) {
        if( $index == $cix1sC ) {
          $this->components[$cix] = $component;
          return TRUE;
        }
        $cix1sC++;
      }
      elseif( !$argType && ( $arg1 == $component2->getProperty( 'uid' ))) {
        $this->components[$cix] = $component;
        return TRUE;
      }
    }
            /* not found.. . insert ?? .. .*/
    $this->components[] = $component;
  }
/**
 * parse iCal file into vcalendar, components, properties and parameters
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 1.x.x - 2007-05-17
 * @param string $filename optional filname (incl. opt. directory/path)
 * @return bool FALSE if error occurs during parsing
 *
 */
  function parse( $filename=FALSE ) {
    if( !$filename )
      $filename = $this->getConfig( 'dirfile' );
    else {
      $filename2 = basename( $filename );
      $directory = str_replace( $filename2, '', $filename );
      if( $this->getConfig( 'delimiter' ) == substr( $directory, -1 ))
        $directory = substr( $directory, 0, strlen( $directory ) -1 );
      $this->setConfig( 'filename', $filename2 );
      if( !empty( $directory ))
        $this->setConfig( 'directory', $directory );
      else
        $this->setConfig( 'directory', '.' );
    }
            /* file error tests */
    if( !is_file( $filename ))      /* err 1 */
      return FALSE;
    if( !is_readable( $filename ))
      return FALSE;                 /* err 2 */
    if( !filesize( $filename ))
      return FALSE;                 /* err 3 */
    $rows = file( $filename );
            /* identify and remove begin/end calendar rows */
    if(( 'BEGIN:VCALENDAR' != strtoupper( trim( $rows[0] ))) &&
       ( 'END:VCALENDAR'   != strtoupper( trim( $rows[count($rows)-1]))))
      return FALSE;                 /* err 4 */
    else {
      unset( $rows[count($rows)-1] );
      unset( $rows[0] );
      $rows = array_values( $rows );
      if( 1 > count( $rows ))
        return FALSE;               /* err 5 */
    }
    $comp = $subcomp = null;
    $actcomp = & $this;
            /* identify components and update unparsed data within component */
    foreach( $rows as $line ) {
      if( "\n" == substr( $line, -1 ))
        $line = substr( $line, 0, strlen( $line ) -1 ).'\n';
      if( 'END:' == strtoupper( substr( $line, 0, 4 ))) {
        if( null != $subcomp ) {
          $comp->setComponent( $subcomp );
          $subcomp = null;
        }
        else {
          $this->setComponent( $comp );
          $comp = null;
        }
        $actcomp = null;
        continue;
      } // end - if ( 'END:' ==.. .
      elseif( 'BEGIN:' == strtoupper( substr( $line, 0, 6 ))) {
        $line = str_replace( '\n', '', $line );
        $compname = strtoupper( substr( $line, 6 ));
        if( null != $comp ) {
          if( 'VALARM' == $compname )
            $subcomp = new valarm();
          elseif( 'STANDARD' == $compname )
            $subcomp = new vtimezone( 'STANDARD' );
          elseif( 'DAYLIGHT' == $compname )
            $subcomp = new vtimezone( 'DAYLIGHT' );
          else
            return FALSE; /* err 6 */
          $actcomp = & $subcomp;
        }
        else {
          switch( $compname ) {
            case 'VALARM':
              $comp = new valarm();
              break;
            case 'VEVENT':
              $comp = new vevent();
              break;
            case 'VFREEBUSY';
              $comp = new vfreebusy();
              break;
            case 'VJOURNAL':
              $comp = new vjournal();
              break;
            case 'VTODO':
              $comp = new vtodo();
              break;
            case 'VTIMEZONE':
              $comp = new vtimezone();
              break;
            default:
              return FALSE; // err 7
              break;
          } // end - switch
          $actcomp = & $comp;
        }
        continue;
      } // end - elsif ( 'BEGIN:'.. .
            /* update selected component with unparsed data */
      $actcomp->unparsed[] = $line;
    } // end - foreach( rows.. .
            /* parse data for calendar (this) object */
    if( is_array( $this->unparsed ) && ( 0 < count( $this->unparsed ))) {
            /* concatenate property values spread over several lines */
      $lastix    = -1;
      $propnames = array( 'calscale','method','prodid','version','x-' );
      $unparsed  = array();
      foreach( $this->unparsed as $line ) {
        $newProp = FALSE;
        foreach ( $propnames as $propname ) {
          if( $propname == strtolower( substr( $line, 0, strlen( $propname )))) {
            $newProp = TRUE;
            break;
          }
        }
        if( $newProp ) {
          $newProp = FALSE;
          $lastix++;
          $unparsed[$lastix]  = $line;
        }
        else {
            /* remove line breaks */
          if(( '\n' == substr( $unparsed[$lastix], - 2 )) &&
             (  ' ' == substr( $line, 0, 1 ))) {
            $unparsed[$lastix] = substr( $unparsed[$lastix], 0, strlen( $unparsed[$lastix] ) - 2 );
            $line = substr( $line, 1 );
          }
          $unparsed[$lastix] .= $line;
        }
      }
      foreach( $unparsed as $line ) {
        if( '\n' == substr( $line, - 2 ))
          $line = substr( $line, 0, strlen( $line ) - 2 );
            /* get propname */
        $cix = $propname = null;
        for( $cix=0; $cix < strlen( $line ); $cix++ ) {
          if( in_array( $line{$cix}, array( ':', ';' )))
            break;
          else
            $propname .= $line{$cix};
        }
            /* ignore version/prodid properties */
        if( in_array( strtoupper( $propname ), array( 'VERSION', 'PRODID' )))
          continue;
        $line = substr( $line, $cix);
            /* separate attributes from value */
        $attr   = array();
        $attrix = -1;
        for( $cix=0; $cix < strlen( $line ); $cix++ ) {
          if((       ':'   == $line{$cix} ) &&
             (       '://' != substr( $line, $cix, 3 )) &&
             ( 'mailto:'   != strtolower( substr( $line, $cix - 6, 7 )))) {
            $attrEnd = TRUE;
            if( ctype_digit( $line{$cix+1} )) { // an URI with a portnr??
              for( $c2ix = $cix; 3 < $c2ix; $c2ix-- ) {
                if( '://' == substr( $line, $c2ix - 2, 3 )) {
                  $attrEnd = FALSE;
                  break; // an URI with a portnr!!
                }
              }
            }
            if( $attrEnd) {
              $line = substr( $line, $cix + 1 );
              break;
            }
          }
          if( ';' == $line{$cix} )
            $attr[++$attrix] = null;
          else
            $attr[$attrix] .= $line{$cix};
        }

            /* make attributes in array format */
        $propattr = array();
        foreach( $attr as $attribute ) {
          $attrsplit = explode( '=', $attribute, 2 );
          if( 1 < count( $attrsplit ))
            $propattr[$attrsplit[0]] = $attrsplit[1];
          else
            $propattr[] = $attribute;
        }
            /* update Property */
        $this->setProperty( $propname, $line, $propattr );
      } // end - foreach( $this->unparsed.. .
    } // end - if( is_array( $this->unparsed.. .
            /* parse Components */
    if( is_array( $this->components ) && ( 0 < count( $this->components ))) {
      for( $six = 0; $six < count( $this->components ); $six++ )
        $this->components[$six]->parse();
    }
  }
/*********************************************************************************/
/**
 * creates formatted output for calendar object instance
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.22 - 2007-04-07
 * @return string
 */
  function createCalendar() {
    $calendarInit1 = $calendarInit2 = $calendarxCaldecl = $calendarStart = $calendar = null;
    switch( $this->format ) {
      case 'xcal':
        $calendarInit1 = '<?xml version="1.0" encoding="UTF-8"?>'.$this->nl.
                         '<!DOCTYPE iCalendar PUBLIC "-//IETF//DTD XCAL/iCalendar XML//EN"'.$this->nl.
                         '"http://www.ietf.org/internet-drafts/draft-ietf-calsch-many-xcal-01.txt"';
        $calendarInit2 = '>'.$this->nl;
        $calendarStart = '<vcalendar';
        break;
      default:
        $calendarStart = 'BEGIN:VCALENDAR'.$this->nl;
        break;
    }
    $calendarStart .= $this->createCalscale();
    $calendarStart .= $this->createMethod();
    $calendarStart .= $this->createProdid();
    $calendarStart .= $this->createVersion();
    switch( $this->format ) {
      case 'xcal':
        $nlstrlen = strlen( $this->nl );
        if( $this->nl == substr( $calendarStart, ( 0 - $nlstrlen )))
          $calendarStart = substr( $calendarStart, 0, ( strlen( $calendarStart ) - $nlstrlen ));
        $calendarStart .= '>'.$this->nl;
        break;
      default:
        break;
    }
    $calendar .= $this->createXprop();
    foreach( $this->components as $component ) {
      if( '' >= $component->getConfig( 'language'))
        $component->setConfig( 'language',  $this->getConfig( 'language' ));
      $component->setConfig( 'nl',          $this->getConfig( 'nl' ));
      $component->setConfig( 'unique_id',   $this->getConfig( 'unique_id' ));
      $component->setConfig( 'format',      $this->getConfig( 'format' ));
      $calendar .= $component->createComponent( $this->xcaldecl );
    }
    if(( 0 < count( $this->xcaldecl )) && ( 'xcal' == $this->format )) { // xCal only
      $calendarInit1 .= $this->nl.'['.$this->nl;
      $old_xcaldecl = array();
      foreach( $this->xcaldecl as $declix => $declPart ) {
        if(( 0 < count( $old_xcaldecl)) &&
           ( in_array( $declPart['uri'],      $old_xcaldecl['uri'] )) &&
           ( in_array( $declPart['external'], $old_xcaldecl['external'] )))
          continue; // no duplicate uri and ext. references
        $calendarxCaldecl .= '<!';
        foreach( $declPart as $declKey => $declValue ) {
          switch( $declKey ) {                    // index
            case 'xmldecl':                       // no 1
              $calendarxCaldecl .= $declValue.' ';
              break;
            case 'uri':                           // no 2
              $calendarxCaldecl .= $declValue.' ';
              $old_xcaldecl['uri'][] = $declValue;
              break;
            case 'ref':                           // no 3
              $calendarxCaldecl .= $declValue.' ';
              break;
            case 'external':                      // no 4
              $calendarxCaldecl .= '"'.$declValue.'" ';
              $old_xcaldecl['external'][] = $declValue;
              break;
            case 'type':                          // no 5
              $calendarxCaldecl .= $declValue.' ';
              break;
            case 'type2':                         // no 6
              $calendarxCaldecl .= $declValue;
              break;
          }
        }
        $calendarxCaldecl .= '>'.$this->nl;
      }
      $calendarInit2 = ']'.$calendarInit2;
    }
    switch( $this->format ) {
      case 'xcal':
        $calendar .= '</vcalendar>'.$this->nl;
        break;
      default:
        $calendar .= 'END:VCALENDAR'.$this->nl;
        break;
    }
    return $calendarInit1.$calendarxCaldecl.$calendarInit2.$calendarStart.$calendar;
  }
/**
 * redirect file to user
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.22 - 2007-04-07
 * @return redirect
 */
  function _redirectCalendar() {
    $dirfile = $this->getConfig( 'dirfile' );
//    if( headers_sent( $filename, $linenum ))
//      die( "Headers already sent in $filename on line $linenum\n" );
    if( 'xcal' == $this->format )
      header( 'Content-Type: application/calendar+xml; charset=utf-8' );
    else
      header( 'Content-Type: text/calendar; charset=utf-8' );
    header( 'Content-Length: '.$this->getConfig( 'filesize' ));
    header( 'Content-Disposition: attachment; filename="'.basename( $dirfile ).'"' );
            /* disable caching */
    header( 'Cache-Control: no-cache, no-store, max-age=0, must-revalidate' );
    header( 'Expires: Mon, 26 Jul 1997 05:00:00 GMT' );
    header( 'Pragma: no-cache' );
    $fp = fopen( $dirfile, 'r' );
    fpassthru( $fp );
    fclose( $fp );
    die();
  }
/**
 * a HTTP redirect header is sent with saved calendar
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.22 - 2007-04-07
 * @param string $directory optional
 * @param string $filename optional
 * @param string $delimiter optional
 * @return redirect
 */
  function returnCalendar( $directory=FALSE, $filename=FALSE, $delimiter=DIRECTORY_SEPARATOR ) {
    if( $this->saveCalendar ( $directory, $filename, $delimiter ))
      $this->_redirectCalendar ();
  }
/**
 * save content in a file
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.22 - 2007-04-06
 * @param string $directory optional
 * @param string $filename optional
 * @param string $delimiter optional
 * @return bool
 */
  function saveCalendar( $directory=FALSE, $filename=FALSE, $delimiter=DIRECTORY_SEPARATOR ) {
    if( $directory )
      $this->setConfig( 'directory', $directory );
    if( $filename )
      $this->setConfig( 'filename',  $filename );
    if( $delimiter )
      $this->setConfig( 'delimiter', $delimiter );
    $dirfile = $this->getConfig( 'dirfile' );
    $iCalFile = fopen( $dirfile, 'w' );
    if ( $iCalFile ) {
      fputs( $iCalFile, $this->createCalendar() );
      fclose( $iCalFile );
      return TRUE;
    }
    else
      return FALSE;
  }
/**
 * if recent version of file exists (max one hour), an HTTP redirect header is sent
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 1.x.x - 2007-05-15
 * @param string $directory optional
 * @param string $filename optional
 * @param string $delimiter optional
 * @param int timeout optional, default 36001
 * @return redirect/FALSE
 */
  function useCachedCalendar( $directory=FALSE, $filename=FALSE, $delimiter=DIRECTORY_SEPARATOR, $timeout=3600) {
    if( $directory )
      $this->setConfig( 'directory', $directory );
    if( $filename )
      $this->setConfig( 'filename',  $filename );
    if( $delimiter )
      $this->setConfig( 'delimiter', $delimiter );
    if( 0 >= $this->getConfig( 'filesize' ))
      return FALSE;
    $dirfile = $this->getConfig( 'dirfile' );
    if( time() - filemtime( $dirfile ) < $timeout)
      $this->_redirectCalendar ();
    else
      return FALSE;
  }
}
/*********************************************************************************/
/*********************************************************************************/
/**
 *  abstract class for calendar components
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.8 - 2006-11-27
 */
class calendarComponent {
            //  component property variables
  var $action;
  var $attach;
  var $attendee;
  var $categories;
  var $comment;
  var $completed;
  var $contact;
  var $class;
  var $created;
  var $description;
  var $dtend;
  var $dtstart;
  var $dtstamp;
  var $due;
  var $duration;
  var $exdate;
  var $exrule;
  var $freebusy;
  var $geo;
  var $lastmodified;
  var $location;
  var $organizer;
  var $percentcomplete;
  var $priority;
  var $rdate;
  var $recurrenceid;
  var $relatedto;
  var $repeat;
  var $requeststatus;
  var $resources;
  var $rrule;
  var $sequence;
  var $status;
  var $summary;
  var $transp;
  var $trigger;
  var $tzid;
  var $tzname;
  var $tzoffsetfrom;
  var $tzoffsetto;
  var $tzurl;
  var $uid;
  var $url;
  var $xprop;
            //  component subcomponents container
  var $subcomponents;
            //  component config variables
  var $language;
  var $nl;
  var $unique_id;
  var $format;
  var $objName; // created automatically at instance creation
            //  component internal variables
  var $componentStart1;
  var $componentStart2;
  var $componentEnd1;
  var $componentEnd2;
  var $elementStart1;
  var $elementStart2;
  var $elementEnd1;
  var $elementEnd2;
  var $intAttrDelimiter;
  var $attributeDelimiter;
  var $valueInit;
            //  component xCal declaration container
  var $xcaldecl;
/**
 * constructor for calendar component object
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.8.5 - 2006-10-06
 */
  function calendarComponent() {
    $this->objName         = get_class ( $this );

    $this->action          = array();
    $this->attach          = array();
    $this->attendee        = array();
    $this->categories      = array();
    $this->class           = array();
    $this->comment         = array();
    $this->completed       = array();
    $this->contact         = array();
    $this->created         = array();
    $this->description     = array();
    $this->dtend           = array();
    $this->dtstart         = array();
    $this->dtstamp         = array();
    $this->due             = array();
    $this->duration        = array();
    $this->exdate          = array();
    $this->exrule          = array();
    $this->freebusy        = array();
    $this->geo             = array();
    $this->lastmodified    = array();
    $this->location        = array();
    $this->organizer       = array();
    $this->percentcomplete = array();
    $this->priority        = array();
    $this->rdate           = array();
    $this->recurrenceid    = array();
    $this->relatedto       = array();
    $this->repeat          = array();
    $this->requeststatus   = array();
    $this->resources       = array();
    $this->sequence        = array();
    $this->rrule           = array();
    $this->status          = array();
    $this->summary         = array();
    $this->transp          = array();
    $this->trigger         = array();
    $this->tzid            = array();
    $this->tzname          = array();
    $this->tzoffsetfrom    = array();
    $this->tzoffsetto      = array();
    $this->tzurl           = array();
    $this->uid             = array();
    $this->url             = array();
    $this->xprop           = array();

    $this->subcomponents   = array();

    $this->language        = null;
    $this->nl              = null;
    $this->unique_id       = null;
    $this->xcaldecl        = array();

    $this->_makeDtstamp();
  }
/*********************************************************************************/
/**
 * Property Name: ACTION
 */
/**
 * creates formatted output for calendar component property action
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.7 - 2006-11-20
 * @return string
 */
  function createAction() {
    if( !isset( $this->action['value'] ))
      return;
    $attributes = $this->_createParams( $this->action['params'] );
    return $this->_createElement( 'ACTION', $attributes, $this->action['value'] );
  }
/**
 * set calendar component property action
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.2 - 2006-11-16
 * @param string $value  "AUDIO" / "DISPLAY" / "EMAIL" / "PROCEDURE"
 * @param mixed $params
 * @return void
 */
  function setAction( $value, $params=FALSE ) {
    $this->action['value']  = $value;
    $this->action['params'] = $this->_setParams( $params );
  }
/*********************************************************************************/
/**
 * Property Name: ATTACH
 */
/**
 * creates formatted output for calendar component property attach
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.7 - 2006-11-20
 * @return string
 */
  function createAttach() {
    $cnt = count( $this->attach );
    if( 0 >= $cnt )
      return;
    $output    = null;
    foreach( $this->attach as $attachPart ) {
      $attributes = $this->_createParams( $attachPart['params'] );
      $output    .= $this->_createElement( 'ATTACH', $attributes, $attachPart['value'] );
    }
    return $output;
  }
/**
 * set calendar component property attach
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.22 - 2007-04-10
 * @param string $value
 * @param string $params
 * @return void
 */
  function setAttach( $value, $params=FALSE) {
    $attach           = array();
    $attach['value']  = $value ;
    $attach['params'] = $this->_setParams( $params );
    $this->attach[]   = $attach;
  }
/*********************************************************************************/
/**
 * Property Name: ATTENDEE
 */
/**
 * creates formatted output for calendar component property attendee
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 1.x.x - 2007-05-20
 * @return string
 */
  function createAttendee() {
    $cnt = count( $this->attendee );
    if( 0 >= $cnt )
      return;
    $attendees = null;
    foreach( $this->attendee as $attendeePart ) {                      // start foreach 1
      $attendee1 = $attendee2 = $attendeeLANG = $attendeeCN = null;
      foreach( $attendeePart as $paramlabel => $paramvalue ) {         // start foreach 2
        if( 'value' == $paramlabel ) {
          $attendee2  .= 'MAILTO:'.$paramvalue;
        }
        elseif(( 'params' == $paramlabel ) && ( is_array( $paramvalue ))) { // start elseif
          foreach( $paramvalue as $optparamlabel => $optparamvalue ) { // start foreach 3
            $attendee11 = $attendee12 = null;
            if( is_int( $optparamlabel )) {
              $attendee1 .= $this->intAttrDelimiter.$optparamvalue;
              continue;
            }
            switch( $optparamlabel ) {                                 // start switch
              case 'CUTYPE':
              case 'PARTSTAT':
              case 'ROLE':
              case 'RSVP':
                $attendee1 .= $this->intAttrDelimiter.$optparamlabel.'="'.$optparamvalue.'"';
                break;
              case 'SENT-BY':
                $attendee1 .= $this->intAttrDelimiter.'SENT-BY="MAILTO:'.$optparamvalue.'"';
                break;
              case 'MEMBER':
                $attendee11 = $this->intAttrDelimiter.'MEMBER=';
              case 'DELEGATED-TO':
                $attendee11 = ( !$attendee11 ) ? $this->intAttrDelimiter.'DELEGATED-TO='   : $attendee11;
              case 'DELEGATED-FROM':
                $attendee11 = ( !$attendee11 ) ? $this->intAttrDelimiter.'DELEGATED-FROM=' : $attendee11;
                foreach( $optparamvalue  as $cix => $calUserAddress ) {
                  $attendee12 .= ( $cix ) ? ',' : null;
                  $attendee12 .= '"MAILTO:'.$calUserAddress.'"';
                }
                $attendee1  .= $attendee11.$attendee12;
                break;
              case 'CN':
                $attendeeCN .= $this->intAttrDelimiter.'CN="'.$optparamvalue.'"';
                break;
              case 'DIR':
                $attendee1 .= $this->intAttrDelimiter.'DIR="'.$optparamvalue.'"';
                break;
              case 'LANGUAGE':
                $attendeeLANG .= $this->intAttrDelimiter.'LANGUAGE='.$optparamvalue;
                break;
              default:
                $attendee1 .= $this->intAttrDelimiter."$optparamlabel=$optparamvalue";
                break;
            }    // end switch
          }      // end foreach 3
        }        // end elseif
      }          // end foreach 2
      $attendees .= $this->_createElement( 'ATTENDEE', $attendee1.$attendeeLANG.$attendeeCN, $attendee2 );
    }              // end foreach 1
    return $attendees;
  }
/**
 * set calendar component property attach
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 1.x.x - 2007-05-24
 * @param string $value
 * @param array $params optional
 * @return void
 */
  function setAttendee( $value, $params=FALSE ) {
    $value = str_replace ( 'MAILTO:', '', $value );
    $value = str_replace ( 'mailto:', '', $value );
    $attendee = array( 'value' => $value, 'params' => array() );
    if( is_array($params )) {
      $optarrays = array();
      foreach( $params as $optparamlabel => $optparamvalue ) {
        $optparamlabel = strtoupper( $optparamlabel );
        switch( $optparamlabel ) {
          case 'MEMBER':
          case 'DELEGATED-TO':
          case 'DELEGATED-FROM':
            if( is_array( $optparamvalue )) {
              foreach( $optparamvalue as $part ) {
                $part = str_replace( 'MAILTO:', '', $part );
                $part = str_replace( 'mailto:', '', $part );
                if(( '"' == $part{0} ) && ( '"' == $part{strlen($part)-1} ))
                  $part = substr( $part, 1, ( strlen($part)-2 ));
                $optarrays[$optparamlabel][] = $part;
              }
            }
            else {
              $part = str_replace( 'MAILTO:', '', $optparamvalue );
              $part = str_replace( 'mailto:', '', $part );
              if(( '"' == $part{0} ) && ( '"' == $part{strlen($part)-1} ))
                $part = substr( $part, 1, ( strlen($part)-2 ));
              $optarrays[$optparamlabel][] = $part;
            }
            break;
          default:
            if( 'SENT-BY' ==  $optparamlabel ) {
              $optparamvalue = str_replace( 'MAILTO:', '', $optparamvalue );
              $optparamvalue = str_replace( 'mailto:', '', $optparamvalue );
            }
            if(( '"' == substr( $optparamvalue, 0, 1 )) &&
               ( '"' == substr( $optparamvalue, -1 )))
              $optparamvalue = substr( $optparamvalue, 1, ( strlen( $optparamvalue ) - 2 ));
            $attendee['params'][$optparamlabel] = $optparamvalue;
            break;
        } // end switch( $optparamlabel.. .
      } // end foreach( $optparam.. .
      foreach( $optarrays as $optparamlabel => $optparams )
        $attendee['params'][$optparamlabel] = $optparams;
    }
        // remove defaults
    if( isset( $attendee['params']['CUTYPE' ] )  &&
       ( strtoupper( $attendee['params']['CUTYPE' ] )  == 'INDIVIDUAL' ))
      unset( $attendee['params']['CUTYPE' ] );
    if( isset( $attendee['params']['PARTSTAT'] ) &&
       ( strtoupper( $attendee['params']['PARTSTAT'] ) == 'NEEDS-ACTION' ))
      unset( $attendee['params']['PARTSTAT'] );
    if( isset( $attendee['params']['ROLE'] )     &&
       ( strtoupper( $attendee['params']['ROLE'] )     == 'REQ-PARTICIPANT' ))
      unset( $attendee['params']['ROLE'] );
    if( isset( $attendee['params']['RSVP'] )     &&
       ( strtoupper( $attendee['params']['RSVP'] )     == 'FALSE' ))
      unset( $attendee['params']['RSVP'] );
        // check language setting
    if( isset( $attendee['params']['CN' ] )) {
      $lang = $this->getConfig( 'language' );
      if( !isset( $attendee['params']['LANGUAGE' ] ) && !empty( $lang ))
        $attendee['params']['LANGUAGE' ] = $lang;
    }
    $this->attendee[] = $attendee;
  }
/*********************************************************************************/
/**
 * Property Name: CATEGORIES
 */
/**
 * creates formatted output for calendar component property categories
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 1.x.x - 2007-05-18
 * @return string
 */
  function createCategories() {
    $cnt = count( $this->categories );
    if( 0 >= $cnt )
      return;
    $output = null;
    foreach( $this->categories as $category ) {
      $attributes = $this->_createParams( $category['params'], array( 'LANGUAGE' ));
      $output    .= $this->_createElement( 'CATEGORIES', $attributes, $category['value'] );
    }
    return $output;
  }
/**
 * set calendar component property categories
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 1.x.x - 2007-05-18
 * @param mixed $value
 * @param array $params optional
 * @return void
 */
  function setCategories( $value, $params=FALSE ) {
    $category = array();
    $value    = ( is_array( $value )) ? implode( ',', $value ) : $value;
    $value    = str_replace( ', ', ',', $value );
    $value    = str_replace( ' ,', ',', $value );
    $category['value']  = $value;
    $category['params'] = $this->_setParams( $params );
    $this->categories[]   = $category;
  }
/*********************************************************************************/
/**
 * Property Name: CLASS
 */
/**
 * creates formatted output for calendar component property class
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.7 - 2006-11-20
 * @return string
 */
  function createClass() {
    $cnt = count( $this->class );
    if( 0 >= $cnt )
      return;
    $attributes = $this->_createParams( $this->class['params'] );
    return $this->_createElement( 'CLASS', $attributes, $this->class['value'] );
  }
/**
 * set calendar component property class
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.18 - 2003-03-18
 * @param string $value "PUBLIC" / "PRIVATE" / "CONFIDENTIAL" / iana-token / x-name
 * @param array $params optional
 * @return void
 */
  function setClass( $value, $params=FALSE ) {
    $this->class['value']  = $value;
    $this->class['params'] = $this->_setParams( $params );
  }
/*********************************************************************************/
/**
 * Property Name: COMMENT
 */
/**
 * creates formatted output for calendar component property comment
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.7 - 2006-11-20
 * @return string
 */
  function createComment() {
    $cnt = count( $this->comment );
     if( 0 >= $cnt )
      return;
    $comment = null;
    foreach( $this->comment as $commentPart ) {
      $attributes = $this->_createParams( $commentPart['params'], array( 'ALTREP', 'LANGUAGE' ));
      $content    = $this->_strrep( $commentPart['value'] );
      $comment   .= $this->_createElement( 'COMMENT', $attributes, $content );
    }
    return $comment;
  }
/**
 * set calendar component property comment
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.18 - 2007-03-18
 * @param string $value
 * @param array $params optional
 * @return void
 */
  function setComment( $value, $params=FALSE ) {
    $comment['value']  = $value;
    $comment['params'] = $this->_setParams( $params );
    $this->comment[]   = $comment;
  }
/*********************************************************************************/
/**
 * Property Name: COMPLETED
 */
/**
 * creates formatted output for calendar component property completed
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.7 - 2006-11-20
 * @return string
 */
  function createCompleted( ) {
    if( !isset( $this->completed['value']['year'] )  &&
        !isset( $this->completed['value']['month'] ) &&
        !isset( $this->completed['value']['day'] )   &&
        !isset( $this->completed['value']['hour'] )  &&
        !isset( $this->completed['value']['min'] )   &&
        !isset( $this->completed['value']['sec'] ))
      return;
    $formatted  = $this->_format_date_time( $this->completed['value'], 7 );
    $attributes = $this->_createParams( $this->completed['params'] );
    return $this->_createElement( 'COMPLETED', $attributes, $formatted );
  }
/**
 * set calendar component property completed
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.18 - 2007-03-18
 * @param mixed $year
 * @param mixed $month optional
 * @param int $day optional
 * @param int $hour optional
 * @param int $min optional
 * @param int $sec optional
 * @param array $params optional
 * @return void
 */
  function setCompleted( $year, $month=FALSE, $day=FALSE, $hour=FALSE, $min=FALSE, $sec=FALSE, $params=FALSE ) {
    if( is_array( $year ) &&
      (( 6 == count( $year )) ||
       ( array_key_exists( 'year', $year )))) {
      $this->completed['value']  = $this->_date_time_array( $year, 6 );
      $this->completed['params'] = $this->_setParams( $month );
    }
    elseif( is_array( $year ) && isset( $year['timestamp'] )) {
      $this->completed['value']  = $this->_date_time_string( date( 'Y-m-d H:i:s', $year['timestamp'] ), 6 );
      $this->completed['params'] = $this->_setParams( $month );
    }
    elseif( 8 <= strlen( trim( $year ))) { // ex. 2006-08-03 10:12:18
      $this->completed['value']  = $this->_date_time_string( $year, 6 );
      $this->completed['params'] = $this->_setParams( $month );
    }
    else {
      $this->completed['value']  = array('year'  => $year
                                       , 'month' => $month
                                       , 'day'   => $day
                                       , 'hour'  => $hour
                                       , 'min'   => $min
                                       , 'sec'   => $sec);
      $this->completed['params'] = $this->_setParams( $params );
    }
    if( !isset( $this->completed['value']['hour'] ))
      $this->completed['value']['hour'] = 0;
    if( !isset( $this->completed['value']['min'] ))
      $this->completed['value']['min'] = 0;
    if( !isset( $this->completed['value']['sec'] ))
      $this->completed['value']['sec'] = 0;
    $this->completed['value']['tz'] = 'Z';
  }
/*********************************************************************************/
/**
 * Property Name: CONTACT
 */
/**
 * creates formatted output for calendar component property contact
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 1.x.x - 2007-05-24
 * @return string
 */
  function createContact() {
    $cnt = count( $this->contact );
    if( 0 >= $cnt )
      return;
    $output = null;
    foreach( $this->contact as $contact ) {
      $attributes = $this->_createParams( $contact['params'], array( 'ALTREP', 'LANGUAGE' ));
      $content    = $this->_strrep( $contact['value'] );
      $output    .= $this->_createElement( 'CONTACT', $attributes, $content );
    }
    return $output;
  }
/**
 * set calendar component property contact
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 1.x.x - 2007-05-24
 * @param string $value
 * @param array $params optional
 * @return void
 */
  function setContact( $value, $params=FALSE ) {
    $contact['value']  = $value;
    $contact['params'] = $this->_setParams( $params );
    if( isset( $contact['params']['ALTREP'] ) &&
       ( '"' == substr( $contact['params']['ALTREP'], 0, 1 )) &&
       ( '"' == substr( $contact['params']['ALTREP'], -1 )))
      $contact['params']['ALTREP'] =
        substr( $contact['params']['ALTREP'], 1, ( strlen( $contact['params']['ALTREP'] ) - 2 ));
    $this->contact[] = $contact;
  }
/*********************************************************************************/
/**
 * Property Name: CREATED
 */
/**
 * creates formatted output for calendar component property created
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.7 - 2006-11-20
 * @return string
 */
  function createCreated() {
    if( !isset( $this->created['value']['year'] )  &&
        !isset( $this->created['value']['month'] ) &&
        !isset( $this->created['value']['day'] )   &&
        !isset( $this->created['value']['hour'] )  &&
        !isset( $this->created['value']['min'] )   &&
        !isset( $this->created['value']['sec'] ))
      return;
    $formatted  = $this->_format_date_time( $this->created['value'], 7 );
    $attributes = $this->_createParams( $this->created['params'] );
    return $this->_createElement( 'CREATED', $attributes, $formatted );
  }
/**
 * set calendar component property created
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 1.x.x - 2007-05-17
 * @param mixed $year optional
 * @param mixed $month optional
 * @param int $day optional
 * @param int $hour optional
 * @param int $min optional
 * @param int $sec optional
 * @param mixed $params optional
 * @return void
 */
  function setCreated( $year=FALSE, $month=FALSE, $day=FALSE, $hour=FALSE, $min=FALSE, $sec=FALSE, $params=FALSE ) {
    if( !isset( $year )) {
      $year = date('Ymd\THis', mktime ( date( 'H' ), date( 'i' ), date( 's' ) - date( 'Z'), date( 'm' ), date( 'd' ), date( 'Y' )));
    }
    if( is_array( $year ) &&
      (( 6 == count( $year )) ||
       ( array_key_exists( 'year', $year )))) {
      $this->created['value']  = $this->_date_time_array( $year, 7 );
      $this->created['params'] = $this->_setParams( $month );
    }
    elseif( is_array( $year ) && isset( $year['timestamp'] )) {
      $this->created['value']  = $this->_date_time_string( date( 'Y-m-d H:i:s', $year['timestamp'] ), 6 );
      $this->created['params'] = $this->_setParams( $month );
    }
    elseif( 8 <= strlen( trim( $year ))) { // ex. 2006-08-03 10:12:18
      $this->created['value']  = $this->_date_time_string( $year, 7 );
      $this->created['params'] = $this->_setParams( $month );
    }
    else {
      $this->created['value']  = array( 'year'  => $year
                                      , 'month' => $month
                                      , 'day'   => $day
                                      , 'hour'  => $hour
                                      , 'min'   => $min
                                      , 'sec'   => $sec );
      $this->created['params'] = $this->_setParams( $params );
    }
    if( !isset( $this->created['value']['hour'] ))
      $this->created['value']['hour'] = 0;
    if( !isset( $this->created['value']['min'] ))
      $this->created['value']['min'] = 0;
    if( !isset( $this->created['value']['sec'] ))
      $this->created['value']['sec'] = 0;
    $this->created['value']['tz'] = 'Z';
  }
/*********************************************************************************/
/**
 * Property Name: DESCRIPTION
 */
/**
 * creates formatted output for calendar component property description
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.7 - 2006-11-20
 * @return string
 */
  function createDescription() {
    $cnt = count( $this->description );
    if( 0 >= $cnt )
      return;
    $descriptions    = null;
    foreach( $this->description as $description ) {
      $attributes    = $this->_createParams( $description['params'], array( 'ALTREP', 'LANGUAGE' ));
      $content       = $this->_strrep( $description['value'] );
      $descriptions .= $this->_createElement( 'DESCRIPTION', $attributes, $content );
    }
    return $descriptions;
  }
/**
 * set calendar component property description
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.18 - 2007-03-18
 * @param string $value
 * @param array $params optional
 * @return void
 */
  function setDescription( $value, $params=FALSE ) {
    $description['value']  = $value;
    $description['params'] = $this->_setParams( $params );
    $this->description[]   = $description;
  }
/*********************************************************************************/
/**
 * Property Name: DTEND
 */
/**
 * creates formatted output for calendar component property dtend
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.19 - 2007-03-27
 * @return string
 */
  function createDtend() {
    if( !isset( $this->dtend['value']['year'] )  &&
        !isset( $this->dtend['value']['month'] ) &&
        !isset( $this->dtend['value']['day'] )   &&
        !isset( $this->dtend['value']['hour'] )  &&
        !isset( $this->dtend['value']['min'] )   &&
        !isset( $this->dtend['value']['sec'] ))
      return;
    $formatted  = $this->_format_date_time( $this->dtend['value'] );
    $attributes = $this->_createParams( $this->dtend['params'] );
    return $this->_createElement( 'DTEND', $attributes, $formatted );
  }
/**
 * set calendar component property dtend
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 1.x.x - 2007-05-13
 * @param mixed $year
 * @param mixed $month optional
 * @param int $day optional
 * @param int $hour optional
 * @param int $min optional
 * @param int $sec optional
 * @param string $tz optional
 * @param array params optional
 * @return void
 */
  function setDtend( $year, $month=FALSE, $day=FALSE, $hour=FALSE, $min=FALSE, $sec=FALSE, $tz=FALSE, $params=FALSE ) {
    $parno = null;
    if( is_array( $year ) && ( in_array( count( $year ), array( 3, 4, 6, 7 )))) {
      $this->dtend['params'] = $this->_setParams( $month, array( 'VALUE' => 'DATE-TIME' ));
      if( isset( $this->dtend['params']['TZID'] ))
        $year['tz'] = $this->dtend['params']['TZID'];
      $hitval = ( !empty( $year['tz'] ) || !empty( $year[6] ) || ( 4 == count( $year ))) ? 7 : 6;
      $parno = $this->_existRem( $this->dtend['params'], 'VALUE', 'DATE-TIME', $hitval );
      if( !isset( $parno )) $parno = $this->_existRem( $this->dtend['params'], 'VALUE', 'DATE', 3 );
      if( !isset( $parno )) $parno = count( $year );
      $this->dtend['value']  = $this->_date_time_array( $year, $parno );
    }
    elseif( is_array( $year ) && isset( $year['timestamp'] )) {
      $this->dtend['params'] = $this->_setParams( $month, array( 'VALUE' => 'DATE-TIME' ));
      if( isset( $this->dtend['params']['TZID'] ))
        $year['tz'] = $this->dtend['params']['TZID'];
      $tz    = ( isset( $year['tz'] )) ? ' '.$year['tz'] : null;
      $hitval = ( !empty( $tz )) ? 7 : 6;
      $parno = $this->_existRem( $this->dtend['params'], 'VALUE', 'DATE-TIME', $hitval );
      if( !isset( $parno )) $parno = $this->_existRem( $this->dtend['params'], 'VALUE', 'DATE', 3 );
      if( !isset( $parno )) $parno = $hitval;
      $this->dtend['value']  = $this->_date_time_string( date( 'Y-m-d H:i:s', $year['timestamp'] ).$tz, $parno );
    }
    elseif( 8 <= strlen( trim( $year ))) { // ex. 2006-08-03 10:12:18
      $this->dtend['params'] = $this->_setParams( $month, array( 'VALUE' => 'DATE-TIME' ));
      $parno = $this->_existRem( $this->dtend['params'], 'VALUE', 'DATE-TIME', 7 );
      if( !isset( $parno )) $parno = $this->_existRem( $this->dtend['params'], 'VALUE', 'DATE', 3 );
      $this->dtend['value']  = $this->_date_time_string( $year, $parno );
      if( isset( $this->dtend['params']['TZID'] ))
        $this->dtend['value']['tz'] = $this->dtend['params']['TZID'];
    }
    else {
      $this->dtend['params'] = $this->_setParams( $params, array( 'VALUE' => 'DATE-TIME' ));
      if( isset( $this->dtend['params']['TZID'] ))
        $tz = $this->dtend['params']['TZID'];
      $hitval = ( !empty( $tz )) ? 7 : null;
      $parno = $this->_existRem( $this->dtend['params'], 'VALUE', 'DATE-TIME', $hitval );
      if( !isset( $parno )) $parno = $this->_existRem( $this->dtend['params'], 'VALUE', 'DATE', 3 );
      $this->dtend['value']  = array( 'year'  => $year
                                     ,'month' => $month
                                     ,'day'   => $day );
      if( 3 != $parno ) {
        $this->dtend['value']['hour'] = ( $hour ) ? $hour : '0';
        $this->dtend['value']['min']  = ( $min )  ? $min  : '0';
        $this->dtend['value']['sec']  = ( $sec )  ? $sec  : '0';
        if( !empty( $tz ))
          $this->dtend['value']['tz'] = $tz;
      }
    }
    if( 3 == $parno ) {
      $this->dtend['params']['VALUE'] = 'DATE';
      unset( $this->dtend['value']['tz'] );
      unset( $this->dtend['params']['TZID'] );
    }
    if( !empty( $this->dtend['value']['tz'] )   &&
         ( $this->dtend['value']['tz'] != 'Z' ) &&
      ( !( in_array($this->dtend['value']['tz']{0}, array( '+', '-' )) &&
           ctype_digit( substr( $this->dtend['value']['tz'], 1 ))) &&
        !ctype_digit( $this->dtend['value']['tz'] ) ) ) {
      $this->dtend['params']['TZID'] = $this->dtend['value']['tz'];
      unset( $this->dtend['value']['tz'] );
    }
    elseif( isset( $this->dtend['params']['TZID'] ))
      unset( $this->dtend['params']['TZID'] );
  }
/*********************************************************************************/
/**
 * Property Name: DTSTAMP
 */
/**
 * creates formatted output for calendar component property dtstamp
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.7 - 2006-11-20
 * @return string
 */
  function createDtstamp() {
    if( !isset( $tis->dtstamp['value']['year'] )  &&
        !isset( $this->dtstamp['value']['month'] ) &&
        !isset( $this->dtstamp['value']['day'] )   &&
        !isset( $this->dtstamp['value']['hour'] )  &&
        !isset( $this->dtstamp['value']['min'] )   &&
        !isset( $this->dtstamp['value']['sec'] ))
      $this->_makeDtstamp();
    $formatted  = $this->_format_date_time( $this->dtstamp['value'], 7 );
    $attributes = $this->_createParams( $this->dtstamp['params'] );
    return $this->_createElement( 'DTSTAMP', $attributes, $formatted );
  }
/**
 * computes datestamp for calendar component object instance dtstamp
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 1.x.x - 2007-05-13
 * @return void
 */
  function _makeDtstamp() {
    $this->dtstamp['value'] = array( 'year'  => date( 'Y' )
                                   , 'month' => date( 'm' )
                                   , 'day'   => date( 'd' )
                                   , 'hour'  => date( 'H' )
                                   , 'min'   => date( 'i' )
                                   , 'sec'   => date( 's' ) - date( 'Z' ));
    $this->dtstamp['params'] = null;
  }
/**
 * set calendar component property dtstamp
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.18 - 2007-03-18
 * @param mixed $year
 * @param mixed $month optional
 * @param int $day optional
 * @param int $hour optional
 * @param int $min optional
 * @param int $sec optional
 * @param array $params optional
 * @return void
 */
  function setDtstamp( $year, $month=FALSE, $day=FALSE, $hour=FALSE, $min=FALSE, $sec=FALSE, $params=FALSE ) {
    if( is_array( $year ) &&
      ( array_key_exists( 'year', $year ))) {
      $this->dtstamp['value']  = $this->_date_time_array( $year, 7 );
      $this->dtstamp['params'] = $this->_setParams( $month );
    }
    elseif( is_array( $year ) && isset( $year['timestamp'] )) {
      $this->dtstamp['value']  = $this->_date_time_string( date( 'Y-m-d H:i:s', $year['timestamp'] ), 7 );
      $this->dtstamp['params'] = $this->_setParams( $month );
    }
    elseif( 8 <= strlen( trim( $year ))) { // ex. 2006-08-03 10:12:18
      $this->dtstamp['value']  = $this->_date_time_string( $year, 7 );
      $this->dtstamp['params'] = $this->_setParams( $month );
    }
    else {
      $this->dtstamp['value']  = array( 'year'  => $year
                                      , 'month' => $month
                                      , 'day'   => $day
                                      , 'hour'  => $hour
                                      , 'min'   => $min
                                      , 'sec'   => $sec );
      $this->dtstamp['params'] = $this->_setParams( $params );
    }
    $this->dtstamp['value']['tz']   = 'Z';
  }
/*********************************************************************************/
/**
 * Property Name: DTSTART
 */
/**
 * creates formatted output for calendar component property dtstart
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.7 - 2006-11-20
 * @param bool $localtime optional, default FALSE
 * @return string
 */
  function createDtstart( $localtime=FALSE ) {
    if( !isset( $this->dtstart['value']['year'] )  &&
        !isset( $this->dtstart['value']['month'] ) &&
        !isset( $this->dtstart['value']['day'] )   &&
        !isset( $this->dtstart['value']['hour'] )  &&
        !isset( $this->dtstart['value']['min'] )   &&
        !isset( $this->dtstart['value']['sec'] ))
      return;
    if( $localtime )
      unset( $this->dtstart['value']['tz'] );
    $formatted  = $this->_format_date_time( $this->dtstart['value'] );
    $attributes = $this->_createParams( $this->dtstart['params'] );
    return $this->_createElement( 'DTSTART', $attributes, $formatted );
  }
/**
 * set calendar component property dtstart
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 1.x.x - 2007-05-20
 * @param mixed $year
 * @param mixed $month optional
 * @param int $day optional
 * @param int $hour optional
 * @param int $min optional
 * @param int $sec optional
 * @param string $tz optional
 * @param array $params optional
 * @return void
 */
  function setDtstart( $year, $month=FALSE, $day=FALSE, $hour=FALSE, $min=FALSE, $sec=FALSE, $tz=FALSE, $params=FALSE ) {
    $parno = null;
    if( is_array( $year ) && ( in_array( count( $year ), array( 3, 4, 6, 7 )))) {
      $this->dtstart['params']     = $this->_setParams( $month, array( 'VALUE' => 'DATE-TIME' ));
      if( isset( $this->dtstart['params']['TZID'] ))
        $year['tz'] = $this->dtstart['params']['TZID'];
      $hitval = ( !empty( $year['tz'] ) || !empty( $year[6] ) || ( 4 == count( $year ))) ? 7 : 6;
      $parno = $this->_existRem( $this->dtstart['params'], 'VALUE', 'DATE-TIME', $hitval );
      if( !isset( $parno )) $parno = $this->_existRem( $this->dtstart['params'], 'VALUE', 'DATE', 3 );
      if( !isset( $parno )) $parno = count( $year );
      $this->dtstart['value']      = $this->_date_time_array( $year, $parno );
    }
    elseif( is_array( $year ) && isset( $year['timestamp'] )) {
      $this->dtstart['params']     = $this->_setParams( $month, array( 'VALUE' => 'DATE-TIME' ) );
      if( isset( $this->dtstart['params']['TZID'] ))
        $year['tz'] = $this->dtstart['params']['TZID'];
      $tz    = ( isset( $year['tz'] )) ? ' '.$year['tz'] : null;
      $hitval = ( !empty( $tz )) ? 7 : 6;
      $parno = $this->_existRem( $this->dtstart['params'], 'VALUE', 'DATE-TIME', $hitval );
      if( !isset( $parno )) $parno = $this->_existRem( $this->dtstart['params'], 'VALUE', 'DATE', 3 );
      if( !isset( $parno )) $parno = $hitval;
      $this->dtstart['value']      = $this->_date_time_string( date( 'Y-m-d H:i:s', $year['timestamp'] ).$tz, $parno );
    }
    elseif( 8 <= strlen( trim( $year ))) { // ex. 2006-08-03 10:12:18
      $this->dtstart['params']     = $this->_setParams( $month, array( 'VALUE' => 'DATE-TIME' ) );
      $parno = $this->_existRem( $this->dtstart['params'], 'VALUE', 'DATE-TIME', 7 );
      if( !isset( $parno )) $parno = $this->_existRem( $this->dtstart['params'], 'VALUE', 'DATE', 3 );
      $this->dtstart['value']      = $this->_date_time_string( $year, $parno );
      if( isset( $this->dtstart['params']['TZID'] ))
        $this->dtstart['value']['tz'] = $this->dtstart['params']['TZID'];
    }
    else {
      $this->dtstart['params']     = $this->_setParams( $params, array( 'VALUE' => 'DATE-TIME' ) );
      if( isset( $this->dtstart['params']['TZID'] ))
        $tz    = $this->dtstart['params']['TZID'];
      $hitval = ( !empty( $tz )) ? 7 : null;
      $parno = $this->_existRem( $this->dtstart['params'], 'VALUE', 'DATE-TIME', $hitval );
      if( !isset( $parno )) $parno = $this->_existRem( $this->dtstart['params'], 'VALUE', 'DATE', 3 );
      $this->dtstart['value'] = array( 'year'  => $year, 'month' => $month, 'day'   => $day );
      if( 3 != $parno ) {
        $this->dtstart['value']['hour'] = ( $hour ) ? $hour : '0';
        $this->dtstart['value']['min']  = ( $min )  ? $min  : '0';
        $this->dtstart['value']['sec']  = ( $sec )  ? $sec  : '0';
        if( !empty( $tz ))
          $this->dtstart['value']['tz'] = $tz;
      }
    }
    if( 3 == $parno ) {
      $this->dtstart['params']['VALUE'] = 'DATE';
      unset( $this->dtstart['value']['tz'] );
      unset( $this->dtstart['params']['TZID'] );
    }
    if( !empty( $this->dtstart['value']['tz'] )   &&
         ( $this->dtstart['value']['tz'] != 'Z' ) &&
      ( !( in_array($this->dtstart['value']['tz']{0}, array( '+', '-' )) &&
           ctype_digit( substr( $this->dtstart['value']['tz'], 1 ))) &&
        !ctype_digit( $this->dtstart['value']['tz'] ) ) ) {
      $this->dtstart['params']['TZID'] = $this->dtstart['value']['tz'];
      unset( $this->dtstart['value']['tz'] );
    }
    elseif( isset( $this->dtstart['params']['TZID'] ))
      unset( $this->dtstart['params']['TZID'] );
  }
/*********************************************************************************/
/**
 * Property Name: DUE
 */
/**
 * creates formatted output for calendar component property due
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.19 - 2007-03-27
 * @return string
 */
  function createDue() {
    if( !isset( $this->due['value']['year'] )  &&
        !isset( $this->due['value']['month'] ) &&
        !isset( $this->due['value']['day'] )   &&
        !isset( $this->due['value']['hour'] )  &&
        !isset( $this->due['value']['min'] )   &&
        !isset( $this->due['value']['sec'] ))
      return;
    $formatted  = $this->_format_date_time( $this->due['value'] );
    $attributes = $this->_createParams( $this->due['params'] );
    return $this->_createElement( 'DUE', $attributes, $formatted );
  }
/**
 * set calendar component property due
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 1.x.x - 2007-04-29
 * @param mixed $year
 * @param mixed $month optional
 * @param int $day optional
 * @param int $hour optional
 * @param int $min optional
 * @param int $sec optional
 * @param array $params optional
 * @return void
 */
  function setDue( $year, $month=FALSE, $day=FALSE, $hour=FALSE, $min=FALSE, $sec=FALSE, $tz=FALSE, $params=FALSE ) {
    $parno = null;
    if( is_array( $year ) && ( in_array( count( $year ), array( 3, 4, 6, 7 )))) {
      $this->due['params'] = $this->_setParams( $month, array( 'VALUE' => 'DATE-TIME' ) );
      if( isset( $this->due['params']['TZID'] ))
        $year['tz'] = $this->due['params']['TZID'];
      $hitval = ( !empty( $year['tz'] ) || !empty( $year[6] ) || ( 4 == count( $year ))) ? 7 : 6;
      $parno = $this->_existRem( $this->due['params'], 'VALUE', 'DATE-TIME', $hitval );
      if( !isset( $parno )) $parno  = $this->_existRem( $this->due['params'], 'VALUE', 'DATE', 3 );
      if( !isset( $parno )) $parno  = count( $year );
      $this->due['value'] = $this->_date_time_array( $year, $parno );
    }
    elseif( is_array( $year ) && isset( $year['timestamp'] )) {
      $this->due['params'] = $this->_setParams( $month, array( 'VALUE' => 'DATE-TIME' ) );
      if( isset( $this->due['params']['TZID'] ))
        $year['tz'] = $this->due['params']['TZID'];
      $tz    = ( isset( $year['tz'] )) ? ' '.$year['tz'] : null;
      $hitval = ( !empty( $tz )) ? 7 : 6;
      $parno = $this->_existRem( $this->due['params'], 'VALUE', 'DATE-TIME', $hitval );
      if( !isset( $parno )) $parno  = $this->_existRem( $this->due['params'], 'VALUE', 'DATE', 3 );
      if( !isset( $parno )) $parno  = $hitval;
      $this->due['value'] = $this->_date_time_string( date( 'Y-m-d H:i:s', $year['timestamp'] ).$tz, $parno );
    }
    elseif( 8 <= strlen( trim( $year ))) { // ex. 2006-08-03 10:12:18
      $this->due['params'] = $this->_setParams( $month, array( 'VALUE' => 'DATE-TIME' ) );
      $parno = $this->_existRem( $this->due['params'], 'VALUE', 'DATE-TIME', 7 );
      if( !isset( $parno )) $parno  = $this->_existRem( $this->due['params'], 'VALUE', 'DATE', 3 );
      $this->due['value'] = $this->_date_time_string( $year, $parno );
      if( isset( $this->due['params']['TZID'] ))
        $this->due['value']['tz'] = $this->due['params']['TZID'];
    }
    else {
      $this->due['params'] = $this->_setParams( $params, array( 'VALUE' => 'DATE-TIME' ) );
      if( isset( $this->due['params']['TZID'] ))
        $tz    = $this->due['params']['TZID'];
      $hitval  = ( !empty( $tz )) ? 7 : null;
      $parno   = $this->_existRem( $this->due['params'], 'VALUE', 'DATE-TIME', $hitval );
      if( !isset( $parno )) $parno  = $this->_existRem( $this->due['params'], 'VALUE', 'DATE', 3 );
      $this->due['value'] = array( 'year'  => $year, 'month' => $month, 'day'   => $day );
      if( 3 != $parno ) {
        $this->due['value']['hour'] = ( $hour ) ? $hour : '0';
        $this->due['value']['min']  = ( $min )  ? $min  : '0';
        $this->due['value']['sec']  = ( $sec )  ? $sec  : '0';
        if( !empty( $tz ))
          $this->due['value']['tz'] = $tz;
      }
    }
    if( 3 == $parno ) {
      $this->due['params']['VALUE'] = 'DATE';
      unset( $this->due['value']['tz'] );
      unset( $this->due['params']['TZID'] );
    }
    if( !empty( $this->due['value']['tz'] )   &&
         ( $this->due['value']['tz'] != 'Z' ) &&
      ( !( in_array($this->due['value']['tz']{0}, array( '+', '-' )) &&
           ctype_digit( substr( $this->due['value']['tz'], 1 ))) &&
        !ctype_digit( $this->due['value']['tz'] ) ) ) {
      $this->due['params']['TZID'] = $this->due['value']['tz'];
      unset( $this->due['value']['tz'] );
    }
    elseif( isset( $this->due['params']['TZID'] ))
      unset( $this->due['params']['TZID'] );
  }
/*********************************************************************************/
/**
 * Property Name: DURATION
 */
/**
 * creates formatted output for calendar component property duration
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.7 - 2006-11-20
 * @return string
 */
  function createDuration() {
    if( !isset( $this->duration['value']['week'] ) &&
        !isset( $this->duration['value']['day'] )  &&
        !isset( $this->duration['value']['hour'] ) &&
        !isset( $this->duration['value']['min'] )  &&
        !isset( $this->duration['value']['sec'] ))
      return;
    $attributes = $this->_createParams( $this->duration['params'] );
    return $this->_createElement( 'DURATION', $attributes, $this->_format_duration( $this->duration['value'] ));
  }
/**
 * set calendar component property duration
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.18 - 2007-03-18
 * @param mixed $week
 * @param mixed $day optional
 * @param int $hour optional
 * @param int $min optional
 * @param int $sec optional
 * @param array $params optional
 * @return void
 */
  function setDuration( $week=FALSE, $day=FALSE, $hour=FALSE, $min=FALSE, $sec=FALSE, $params=FALSE ) {
    if( is_array( $week ))  {
      $this->duration['value']  = $this->_duration_array( $week );
      $this->duration['params'] = $this->_setParams( $day );
    }
    elseif( is_string( $week ) && ( 3 <= strlen( trim( $week )))) {
      if( in_array( substr( $week,0, 1 ), array( '+', '-' )))
        $week = substr( $week, 1 );
      $this->duration['value']  = $this->_duration_string( $week );
      $this->duration['params'] = $this->_setParams( $day );
    }
    else {
      $this->duration['value']  = $this->_duration_array( array( $week, $day, $hour, $min, $sec ));
      $this->duration['params'] = $this->_setParams( $params );
    }
  }
/*********************************************************************************/
/**
 * Property Name: EXDATE
 */
/**
 * creates formatted output for calendar component property exdate
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.7 - 2006-11-20
 * @return string
 */
  function createExdate() {
    $cnt = count( $this->exdate );
    if( 0 >= $cnt )
      return;
    $output = null;
    foreach( $this->exdate as $theExdate ) {
      $content = $attributes = null;
      foreach( $theExdate['value'] as $eix => $exdatePart ) {
        $formatted = $this->_format_date_time( $exdatePart );
        if( isset( $theExdate['params']['TZID'] ))
          $formatted = str_replace( 'Z', '', $formatted);
        if( 0 < $eix ) {
          if( isset( $theExdate['value'][0]['tz'] ) &&
            ( ctype_digit( substr( $theExdate['value'][0]['tz'], -4 )) ||
                ( 'Z' == $theExdate['value'][0]['tz'] ))) {
            if( 'Z' != substr( $formatted, -1 ))
              $formatted .= 'Z';
          }
          else
            $formatted = str_replace( 'Z', '', $formatted );
        }
        if( 0 < $eix )
          $content .= ',';
        $content .= $formatted;
      }
      $attributes .= $this->_createParams( $theExdate['params'] );
      $output .= $this->_createElement( 'EXDATE', $attributes, $content );
    }
    return $output;
  }
/**
 * set calendar component property exdate
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.18 - 2007-03-20
 * @param array exdates
 * @param array $params optional
 * @return void
 */
  function setExdate( $exdates, $params=FALSE ) {
    $exdate = array();
    $exdate['params'] = $this->_setParams( $params, array( 'VALUE' => 'DATE-TIME' ) );
    $parno  = ( isset( $exdate['params']['TZID'] )) ? 6 : null;
    $parno  = $this->_existRem( $exdate['params'], 'VALUE', 'DATE-TIME', 7, $parno );
    if( !isset( $parno )) $parno = $this->_existRem( $exdate['params'], 'VALUE', 'DATE', 3 );
    foreach( $exdates as $eix => $theExdate ) {
      if(  is_array( $theExdate ) &&
         ( in_array( count( $theExdate ), array( 3, 4, 6, 7 )))) {
        if( isset( $exdate['params']['TZID'] ))
          $theExdate['tz'] = $exdate['params']['TZID'];
        if( !isset( $parno )) {
          if( 4 < count( $theExdate ))
            $parno = 7;
          else
            $parno = 3;
        }
        $exdatea = $this->_date_time_array( $theExdate, $parno );
      }
      elseif( is_array( $theExdate ) && isset( $theExdate['timestamp'] )) {
        if( isset( $exdate['params']['TZID'] ))
          $theExdate['tz'] = $exdate['params']['TZID'];
        $tz    = ( isset( $theExdate['tz'] )) ? ' '.$theExdate['tz'] : null;
        if( !isset( $parno ))
          $parno = ( !empty( $tz )) ? 7 : 6;
        $exdatea = $this->_date_time_string( date( 'Y-m-d H:i:s', $theExdate['timestamp'] ).$tz, $parno );
      }
      elseif( 8 <= strlen( trim( $theExdate ))) { // ex. 2006-08-03 10:12:18
        $exdatea = $this->_date_time_string( $theExdate, $parno );
        if( isset( $exdate['params']['TZID'] ))
          $exdatea['tz'] = $exdate['params']['TZID'];
      }
      if( !isset( $parno )) {
        $parno = count( $exdatea );
        if( 6 == $parno )
          $parno = 7;
      }
      $exdate['value'][] = $exdatea;
    }
    if( 0 < count( $exdate['value'] )) {
      if( 3 == $parno ) {
        $exdate['params']['VALUE'] = 'DATE';
        foreach( $exdate['value'] as $eix => $exdatea )
          unset( $exdate['value'][$eix]['tz'] );
        unset( $exdate['params']['TZID'] );
      }
      if( !empty( $exdate['value'][0]['tz'] )   &&
           ( $exdate['value'][0]['tz'] != 'Z' ) &&
        ( !( in_array($exdate['value'][0]['tz']{0}, array( '+', '-' )) &&
             ctype_digit( substr( $exdate['value'][0]['tz'], 1 ))) &&
          !ctype_digit( $exdate['value'][0]['tz'] ) ) ) {
        $exdate['params']['TZID'] = $exdate['value'][0]['tz'];
        foreach( $exdate['value'] as $exix => $exdatea ) {
          if( !empty( $exdate['value'][0]['tz'] )   &&
               ( $exdate['value'][0]['tz'] != 'Z' ) &&
            ( !( in_array($exdate['value'][0]['tz']{0}, array( '+', '-' )) &&
                 ctype_digit( substr( $exdate['value'][0]['tz'], 1 ))) &&
              !ctype_digit( $exdate['value'][0]['tz'] ) ) )
            unset( $exdate['value'][$exix]['tz'] );
        }
      }
      elseif( isset( $exdate['params']['TZID'] ))
        unset( $exdate['params']['TZID'] );
      $this->exdate[] = $exdate;
    }
  }
/*********************************************************************************/
/**
 * Property Name: EXRULE
 */
/**
 * creates formatted output for calendar component property exrule
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.7.28 - 2006-09-13
 * @return string
 */
  function createExrule() {
    $cnt = count( $this->exrule );
    if( 0 >= $cnt )
      return;
    $exrule = 'EXRULE';
    return $this->_format_recur( $exrule, $this->exrule );
  }
/**
 * set calendar component property exdate
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.18 - 2007-03-18
 * @param array $exruleset
 * @param array $params optional
 * @return void
 */
  function setExrule( $exruleset, $params=FALSE ) {
    $exrule = array();
    foreach( $exruleset as $exrulelabel => $exrulevalue ) {
      $exrulelabel = strtoupper( $exrulelabel );
      if( 'UNTIL'  != $exrulelabel )
        $exrule['value'][$exrulelabel] = $exrulevalue;
      elseif( is_array( $exrulevalue ) &&
            (( 3 == count( $exrulevalue )) ||
             ( 6 == count( $exrulevalue )) ||
             ( 7 == count( $exrulevalue )) ||
             ( array_key_exists( 'year', $exrulevalue )))) {
        $parno = ( 3 < count( $exrulevalue )) ? 7 : 3 ;
        $date  = $this->_date_time_array( $exrulevalue, $parno );
        if(( 3 < count( $date )) && !isset( $date['tz'] ))
          $date['tz'] = 'Z';
        $exrule['value'][$exrulelabel] = $date;
      }
      elseif( is_array( $exrulevalue ) && isset( $exrulevalue['timestamp'] )) {
        $date  = $this->_date_time_string( date( 'Y-m-d H:i:s', $exrulevalue['timestamp'] ), 6 );
        $date['tz'] = 'Z';
        $exrule['value'][$exrulelabel] = $date;
      }
      elseif( 8 <= strlen( trim( $exrulevalue ))) { // ex. 2006-08-03 10:12:18
        $date = $this->_date_time_string( $exrulevalue );
        if(( 3 < count( $date )) && !isset( $date['tz'] ))
          $date['tz'] = 'Z';
        $exrule['value'][$exrulelabel] = $date;
      }
    }
    $exrule['params'] = $this->_setParams( $params );
    $this->exrule[] = $exrule;
  }
/*********************************************************************************/
/**
 * Property Name: FREEBUSY
 */
/**
 * creates formatted output for calendar component property freebusy
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 1.x.x - 2007-05-16
 * @return string
 */
  function createFreebusy() {
    $cnt = count( $this->freebusy );
    if( 0 >= $cnt )
      return;
    $output = null;
    foreach( $this->freebusy as $freebusyPart ) {
      $attributes = $content = null;
      if( isset( $freebusyPart['value']['fbtype'] )) {
        $attributes .= $this->intAttrDelimiter.'FBTYPE='.$freebusyPart['value']['fbtype'];
        unset( $freebusyPart['value']['fbtype'] );
        $freebusyPart['value'] = array_values( $freebusyPart['value'] );
      }
      else
        $attributes .= $this->intAttrDelimiter.'FBTYPE=BUSY';
      $attributes .= $this->_createParams( $freebusyPart['params'] );
      $fno = 1;
      $cnt = count( $freebusyPart['value']);
      foreach( $freebusyPart['value'] as $periodix => $freebusyPeriod ) {
        $formatted   = $this->_format_date_time( $freebusyPeriod[0] );
        $content .= $formatted;
        $content .= '/';
        $cnt2 = count( $freebusyPeriod[1]);
        if( array_key_exists( 'year', $freebusyPeriod[1] ))      // date-time
          $cnt2 = 7;
        elseif( array_key_exists( 'week', $freebusyPeriod[1] ))  // duration
          $cnt2 = 5;
        if(( 7 == $cnt2 )   &&    // period=  -> date-time
            isset( $freebusyPeriod[1]['year'] )  &&
            isset( $freebusyPeriod[1]['month'] ) &&
            isset( $freebusyPeriod[1]['day'] )) {
          $content .= $this->_format_date_time( $freebusyPeriod[1] );
        }
        else {                                  // period=  -> dur-time
          $content .= $this->_format_duration( $freebusyPeriod[1] );
        }
        if( $fno < $cnt )
          $content .= ',';
        $fno++;
      }
      $output .= $this->_createElement( 'FREEBUSY', $attributes, $content );
    }
    return $output;
  }
/**
 * set calendar component property freebusy
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.18 - 2007-03-18
 * @param string $fbType
 * @param array $fbValues
 * @param array $params optional
 * @return void
 */
  function setFreebusy( $fbType, $fbValues, $params=FALSE ) {
    $fbType = strtoupper( $fbType );
    if(( !in_array( $fbType, array( 'FREE', 'BUSY', 'BUSY-UNAVAILABLE', 'BUSY-TENTATIVE' ))) &&
       ( 'X-' != substr( $fbType, 0, 2 )))
      $fbType = 'BUSY';
    $freebusy['value'] = array( 'fbtype' => $fbType );
    foreach( $fbValues as $fbPeriod ) {   // periods => period
      $freebusyPeriod = array();
      foreach( $fbPeriod as $fbMember ) { // pairs => singlepart
        $freebusyPairMember = array();
        if( is_array( $fbMember )) {
          $cnt = count( $fbMember );
          if(( 6 == $cnt ) || ( 7 == $cnt ) || ( array_key_exists( 'year', $fbMember ))) { // date-time value
            $date = $this->_date_time_array( $fbMember, 7 );
            $date['tz'] = ( !isset( $date['tz'] )) ? 'Z' : $date['tz'];
            $freebusyPairMember = $date;
          }
          elseif( array_key_exists( 'timestamp', $fbMember )) { // timestamp value
            $tz    = ( isset( $fbMember['tz'] )) ? ' '.$fbMember['tz'] : null;
            $parno = ( !empty( $tz )) ? 7 : 6;
            $date  = $this->_date_time_string( date( 'Y-m-d H:i:s', $fbMember['timestamp'] ).$tz, $parno );
            $date['tz'] = ( !isset( $date['tz'] )) ? 'Z' : $date['tz'];
            $freebusyPairMember = $date;
          }
          else {                                         // array format duration
            $freebusyPairMember = $this->_duration_array( $fbMember );
          }
        }
        elseif(( 3 <= strlen( trim( $fbMember ))) &&    // string format duration
               ( in_array( $fbMember{0}, array( 'P', '+', '-' )))) {
          if( 'P' != $fbMember{0} )
            $fbmember = substr( $fbMember, 1 );
          $freebusyPairMember = $this->_duration_string( $fbMember );
        }
        elseif( 8 <= strlen( trim( $fbMember ))) { // ex. 2006-08-03 10:12:18
          $date = $this->_date_time_string( $fbMember, 7 );
          $date['tz'] = ( !isset( $date['tz'] )) ? 'Z' : $date['tz'];
          $freebusyPairMember = $date;
        }
        $freebusyPeriod[]  = $freebusyPairMember;
      }
      $freebusy['value'][] = $freebusyPeriod;
    }
    $freebusy['params']    = $this->_setParams( $params );
    $this->freebusy[]      = $freebusy;
  }
/*********************************************************************************/
/**
 * Property Name: GEO
 */
/**
 * creates formatted output for calendar component property geo
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.7 - 2006-11-20
 * @return string
 */
  function createGeo() {
    $cnt = count( $this->geo );
    if( 0 >= $cnt )
      return;
    $attributes = $this->_createParams( $this->geo['params'] );
    $content    = null;
    $content   .= number_format( (float) $this->geo['value']['latitude'], 6, '.', '');
    $content   .= ';';
    $content   .= number_format( (float) $this->geo['value']['longitude'], 6, '.', '');
    return $this->_createElement( 'GEO', $attributes, $content );
  }
/**
 * set calendar component property geo
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.18 - 2007-03-18
 * @param float $latitude
 * @param float $longitude
 * @param array $params optional
 * @return void
 */
  function setGeo( $latitude, $longitude, $params=FALSE ) {
    $this->geo['value']['latitude']  = $latitude;
    $this->geo['value']['longitude'] = $longitude;
    $this->geo['params'] = $this->_setParams( $params );
  }
/*********************************************************************************/
/**
 * Property Name: LAST-MODIFIED
 */
/**
 * creates formatted output for calendar component property last-modified
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.7 - 2006-11-20
 * @return string
 */
  function createLastModified() {
    if( !isset( $this->lastmodified['value']['year'] )  &&
        !isset( $this->lastmodified['value']['month'] ) &&
        !isset( $this->lastmodified['value']['day'] )   &&
        !isset( $this->lastmodified['value']['hour'] )  &&
        !isset( $this->lastmodified['value']['min'] )   &&
        !isset( $this->lastmodified['value']['sec'] ))
      return;
    $attributes = $this->_createParams( $this->lastmodified['params'] );
    $formatted  = $this->_format_date_time( $this->lastmodified['value'], 7 );
    return $this->_createElement( 'LAST-MODIFIED', $attributes, $formatted );
  }
/**
 * set calendar component property completed
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 1.x.x - 2007-05-13
 * @param mixed $year optional
 * @param mixed $month optional
 * @param int $day optional
 * @param int $hour optional
 * @param int $min optional
 * @param int $sec optional
 * @param array $params optional
 * @return void
 */
  function setLastModified( $year=FALSE, $month=FALSE, $day=FALSE, $hour=FALSE, $min=FALSE, $sec=FALSE, $params=FALSE ) {
    if( !isset( $year )) {
      $year = date('Ymd\THis', mktime ( date( 'H' ), date( 'i' ), date( 's' ) - date( 'Z'), date( 'm' ), date( 'd' ), date( 'Y' )));
    }
    if( is_array( $year ) &&
      (( 6 == count( $year )) || ( isset( $year['year'] )))) {
      $this->lastmodified['value']  = $this->_date_time_array( $year, 7 );
      $this->lastmodified['params'] = $this->_setParams( $month );
    }
    elseif( is_array( $year ) && isset( $year['timestamp'] )) {
      $this->lastmodified['value']  =
        $this->_date_time_string( date( 'Y-m-d H:i:s', $year['timestamp'] ), 7 );
      $this->lastmodified['params'] = $this->_setParams( $month );
    }
    elseif( 8 <= strlen( trim( $year ))) { // ex. 2006-08-03 10:12:18
      $this->lastmodified['value']  = $this->_date_time_string( $year, 7 );
      $this->lastmodified['params'] = $this->_setParams( $month );
    }
    else {
      $this->lastmodified['value']  = array( 'year'  => $year
                                           , 'month' => $month
                                           , 'day'   => $day
                                           , 'hour'  => $hour
                                           , 'min'   => $min
                                           , 'sec'   => $sec
                                           , 'tz'    => 'Z');
      $this->lastmodified['params'] = $this->_setParams( $params );
    }
    if( isset( $this->lastmodified['value']['year'] ) &&
       !isset( $this->lastmodified['value']['tz'] ))
      $this->lastmodified['value']['tz']  = 'Z';
  }
/*********************************************************************************/
/**
 * Property Name: LOCATION
 */
/**
 * creates formatted output for calendar component property location
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.7 - 2006-11-20
 * @return string
 */
  function createLocation() {
    $cnt = count( $this->location );
    if( 0 >= $cnt )
      return;
    $attributes = $this->_createParams( $this->location['params'], array( 'ALTREP', 'LANGUAGE' ));
    $content    = $this->_strrep( $this->location['value'] );
    return $this->_createElement( 'LOCATION', $attributes, $content );
  }
/**
 * set calendar component property location
 '
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.18 - 2007-03-18
 * @param string $value
 * @param array params optional
 * @return void
 */
  function setLocation( $value, $params=FALSE ) {
    $this->location['value']  = $value;
    $this->location['params'] = $this->_setParams( $params );
  }
/*********************************************************************************/
/**
 * Property Name: ORGANIZER
 */
/**
 * creates formatted output for calendar component property organizer
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.7 - 2006-11-20
 * @return string
 */
  function createOrganizer() {
    $cnt = count( $this->organizer );
    if( 0 >= $cnt )
      return;
    $attributes = $this->_createParams( $this->organizer['params']
                                      , array( 'CN', 'DIR', 'LANGUAGE', 'SENT-BY' ));
    $content    = 'MAILTO:'.$this->organizer['value'];
    return $this->_createElement( 'ORGANIZER', $attributes, $content );
  }
/**
 * set calendar component property organizer
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.19 - 2007-04-02
 * @param string $value
 * @param array params optional
 * @return void
 */
  function setOrganizer( $value, $params=FALSE ) {
    $this->organizer['value']  = str_replace( 'MAILTO:', '', $value );
    $this->organizer['params'] = $this->_setParams( $params );
    if( isset($this->organizer['params']['SENT-BY'] )) {
      if( 'MAILTO' == strtoupper( substr( $this->organizer['params']['SENT-BY'], 0, 6 )))
        $this->organizer['params']['SENT-BY'] = substr( $this->organizer['params']['SENT-BY'], 7 );
    }
  }
/*********************************************************************************/
/**
 * Property Name: PERCENT-COMPLETE
 */
/**
 * creates formatted output for calendar component property percent-complete
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.7 - 2006-11-20
 * @return string
 */
  function createPercentComplete() {
    $cnt = count( $this->percentcomplete );
    if( 0 >= $cnt )
      return;
    $attributes = $this->_createParams( $this->percentcomplete['params'] );
    return $this->_createElement( 'PERCENT-COMPLETE', $attributes, $this->percentcomplete['value'] );
  }
/**
 * set calendar component property percent-complete
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.18 - 2007-03-18
 * @param int $value
 * @param array $params optional
 * @return void
 */
  function setPercentComplete( $value, $params=FALSE ) {
    $this->percentcomplete['value']  = $value;
    $this->percentcomplete['params'] = $this->_setParams( $params );
  }
/*********************************************************************************/
/**
 * Property Name: PRIORITY
 */
/**
 * creates formatted output for calendar component property priority
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.7 - 2006-11-20
 * @return string
 */
  function createPriority() {
    $cnt = count( $this->priority );
    if( 0 >= $cnt )
      return;
    $attributes = $this->_createParams( $this->priority['params'] );
    return $this->_createElement( 'PRIORITY', $attributes, $this->priority['value'] );
  }
/**
 * set calendar component property priority
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.18 - 2007-03-18
 * @param int $value
 * @param array $params optional
 * @return void
 */
  function setPriority( $value, $params=FALSE  ) {
    $this->priority['value']  = $value;
    $this->priority['params'] = $this->_setParams( $params );
  }
/*********************************************************************************/
/**
 * Property Name: RDATE
 */
/**
 * creates formatted output for calendar component property rdate
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 1.x.x - 2007-05-13
 * @param bool $localtime optional, default FALSE
 * @return string
 */
  function createRdate( $localtime=FALSE ) {
    $cnt = count( $this->rdate );
    if( 0 >= $cnt )
      return;
    $output = null;
    foreach( $this->rdate as $theRdate ) {
      $attributes = $this->_createParams( $theRdate['params'] );
      $cnt = count( $theRdate['value'] );
      $content = null;
      $rno = 1;
      foreach( $theRdate['value'] as $rpix =>$rdatePart ) {
        $contentPart = null;
        if( is_array( $rdatePart ) &&
           ( 2 == count( $rdatePart )) &&
             array_key_exists( '0', $rdatePart ) &&
             array_key_exists( '1', $rdatePart )) { // PERIOD
          if( $localtime  )
            unset( $rdatePart[0]['tz'] );
          $formatted = $this->_format_date_time( $rdatePart[0]);
          if( isset( $theRdate['params']['TZID'] ))
            $formatted = str_replace( 'Z', '', $formatted);
          if( 0 < $rpix ) {
            if( isset( $rdatePart[0]['tz'] ) &&
               ( ctype_digit( substr( $rdatePart[0]['tz'], -4 )) ||
                  ( 'Z' == $rdatePart[0]['tz'] ))) {
              if( 'Z' != substr( $formatted, -1 ))
                $formatted .= 'Z';
            }
            else
              $formatted = str_replace( 'Z', '', $formatted );
          }
          $contentPart .= $formatted;
          if( 1 == $rno )
            $attributes .= $this->intAttrDelimiter.'VALUE=PERIOD';
          $contentPart .= '/';
          $cnt2 = count( $rdatePart[1]);
          if( array_key_exists( 'year', $rdatePart[1] )) {
            if( array_key_exists( 'hour', $rdatePart[1] ))
              $cnt2 = 7;                                      // date-time
            else
              $cnt2 = 3;                                      // date
          }
          elseif( array_key_exists( 'week', $rdatePart[1] ))  // duration
            $cnt2 = 5;
          if(( 7 == $cnt2 )   &&    // period=  -> date-time
              isset( $rdatePart[1]['year'] )  &&
              isset( $rdatePart[1]['month'] ) &&
              isset( $rdatePart[1]['day'] )) {
            if( $localtime  )
              unset( $rdatePart[1]['tz'] );
            $formatted = $this->_format_date_time( $rdatePart[1] );
            if( isset( $theRdate['params']['TZID'] ))
              $formatted = str_replace( 'Z', '', $formatted);
            if( isset( $rdatePart[0]['tz'] ) &&
              ( ctype_digit( substr( $rdatePart[0]['tz'], -4 )) ||
                  ( 'Z' == $rdatePart[0]['tz'] ))) {
              if( 'Z' != substr( $formatted, -1 ))
                $formatted .= 'Z';
            }
            else
              $formatted = str_replace( 'Z', '', $formatted );
           $contentPart .= $formatted;
          }
          else {                                  // period=  -> dur-time
            $contentPart .= $this->_format_duration( $rdatePart[1] );
          }
        } // PERIOD end
        else { // single date start
          if( $localtime  )
            unset( $rdatePart['tz'] );
          $formatted = $this->_format_date_time( $rdatePart);
          if( isset( $theRdate['params']['TZID'] ))
            $formatted = str_replace( 'Z', '', $formatted);
          if( 0 < $rpix ) {
            $firstPart = ( 2 == count( $theRdate['value'][0] ))
                       ? $theRdate['value'][0][0] : $theRdate['value'][0];
            if( isset( $firstPart['tz'] ) &&
               ( ctype_digit( substr( $firstPart['tz'], -4 )) ||
                  ( 'Z' == $firstPart['tz'] ))) {
              if( 'Z' != substr( $formatted, -1 ))
                $formatted .= 'Z';
            }
            else
              $formatted = str_replace( 'Z', '', $formatted );
          }
          $contentPart .= $formatted;
        }
        $content .= $contentPart;
        if( $rno < $cnt )
          $content .= ',';
        $rno++;
      }
      $output    .= $this->_createElement( 'RDATE', $attributes, $content );
    }
    return $output;
  }
/**
 * set calendar component property rdate
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.18 - 2007-03-18
 * @param array $rdates
 * @param array $params optional
 * @return void
 */
  function setRdate( $rdates, $params=FALSE ) {
    $input = array();
    $input['params'] = $this->_setParams( $params, array( 'VALUE' => 'DATE-TIME' ));
    $this->_existRem( $input['params'], 'VALUE', 'PERIOD' );
    $parno = ( isset( $input['params']['TZID'] )) ? 6 : null;
    $parno = ( !$parno ) ? $this->_existRem( $input['params'], 'VALUE', 'DATE-TIME', 7 ) : null;
    $parno = ( !$parno ) ? $this->_existRem( $input['params'], 'VALUE', 'DATE', 3 ) : 6;
    foreach( $rdates as $theRdate ) {
 //   echo 'setRdate in '; print_r ( $theRdate ); echo "<br />\n"; // test ##
      $inputa = null;
      if( is_array( $theRdate )) {
        if(( 2 == count( $theRdate )) &&
             array_key_exists( '0', $theRdate ) &&
             array_key_exists( '1', $theRdate ) &&
            !array_key_exists( 'timestamp', $theRdate )) { // PERIOD
          foreach( $theRdate as $rix => $rPeriod ) {
  //  echo 'setRdate i2 '; print_r ( $rPeriod ); echo "<br />\n"; // test ##
            if( is_array( $rPeriod )) {
              if (( 1 == count( $rPeriod )) &&
                  ( 8 <= strlen( trim( $rPeriod[0] )))) { // text-date
                $inputab       = $this->_date_time_string( $rPeriod[0], $parno );
                $inputab['tz'] = ( isset( $input['params']['TZID'] )) ? $input['params']['TZID'] :
                                 ( isset( $inputab['tz'] )) ? $inputab['tz'] : null;
                $parno         = ( !isset( $parno )) ? count( $inputab ) : $parno;
                if(( 7 == $parno ) && !isset( $inputab['tz'] ))
                  $inputab['tz'] = 'Z';
                $inputa[] = $inputab;
              }
              elseif (((3 == count( $rPeriod )) && ( $rix < 1 )) ||
                      ( 6 == count( $rPeriod )) ||
                      ( 7 == count( $rPeriod )) ||
                      ( array_key_exists( 'year', $rPeriod ))) { // date[-time] (only 1st rperiod)
                if( !isset( $parno ) && 3 < count( $rPeriod ))
                  $parno = 7;
                $inputab       = $this->_date_time_array( $rPeriod, $parno );
                $inputab['tz'] = ( isset( $input['params']['TZID'] )) ? $input['params']['TZID'] :
                                 ( isset( $inputab['tz'] )) ? $inputab['tz'] : null;
                $parno         = ( !isset( $parno )) ? count( $inputab ) : $parno;
                if(( 7 == $parno ) && !isset( $inputab['tz'] ))
                  $inputab['tz'] = 'Z';
                $inputa[] = $inputab;
              }
              elseif( isset( $rPeriod['timestamp'] )) {   // timestamp
                $tz    = ( isset( $rPeriod['tz'] ))           ? ' '.$rPeriod['tz']           : null;
                $tz    = ( isset( $input['params']['TZID'] )) ? ' '.$input['params']['TZID'] : $tz;
                if( !isset( $parno )) $parno = ( !empty( $tz )) ? 7 : 6;
                $inputab = $this->_date_time_string( date( 'Y-m-d H:i:s', $rPeriod['timestamp'] ).$tz, $parno );
                if(( 7 == $parno ) && !isset( $inputab['tz'] ))
                  $inputab['tz'] = 'Z';
                $inputa[] = $inputab;
              }
              else {                                       // array format duration
                $inputa[] = $this->_duration_array( $rPeriod );
              }
            }
            elseif(( 3 <= strlen( trim( $rPeriod ))) &&    // string format duration
                   ( in_array( $rPeriod{0}, array( 'P', '+', '-' )))) {
              if( 'P' != $rPeriod{0} )
                $rPeriod = substr( $rPeriod, 1 );
              $inputa[] = $this->_duration_string( $rPeriod );
            }
            elseif( 8 <= strlen( trim( $rPeriod ))) { // ex. 2006-08-03 10:12:18
              $inputab       = $this->_date_time_string( $rPeriod, $parno );
              $inputab['tz'] = ( isset( $input['params']['TZID'] ))
                             ? $input['params']['TZID'] :
                               ( isset( $inputab['tz'] )) ? $inputab['tz'] : null;
              $inputa[]      = $inputab;
              $parno         = ( !isset( $parno )) ? count( $inputab ) : $parno;
            }
          }
        }
        elseif ( array_key_exists( 'timestamp', $theRdate )) {   // timestamp
          $tz    = ( isset( $theRdate['tz'] ))          ? ' '.$theRdate['tz']          : null;
          $tz    = ( isset( $input['params']['TZID'] )) ? ' '.$input['params']['TZID'] : $tz;
          if( !isset( $parno )) $parno = ( !empty( $tz )) ? 7 : 6;
          $inputab = $this->_date_time_string( date( 'Y-m-d H:i:s', $theRdate['timestamp'] ).$tz, $parno );
          if(( 7 == $parno ) && !isset( $inputab['tz'] ))
            $inputab['tz'] = 'Z';
          $inputa = $inputab;
        }
        elseif (( in_array( count( $theRdate ), array( 3, 4, 6, 7 ))) ||
                ( array_key_exists( 'year', $theRdate ))) {  // date[-time]
          if( isset( $input['params']['TZID'] ))
            $theRdate['tz'] = $input['params']['TZID'];
          elseif( !isset( $theRdate['tz'] )) {
            if(( 7 == count( $theRdate )) && isset( $theRdate[6] )) {
              $theRdate['tz'] = $theRdate[6];
              unset( $theRdate[6] );
            }
            elseif(( 4 == count( $theRdate )) && isset( $theRdate[3] )) {
              $theRdate['tz'] = $theRdate[3];
              unset( $theRdate[3] );
            }
          }
          if( !isset( $parno ) && 3 < count( $theRdate ))
            $parno = ( isset( $theRdate['tz'] )) ? 7 : count( $theRdate );
          elseif( !isset( $parno ))
            $parno = 3;
          $inputa = $this->_date_time_array( $theRdate, $parno );
          if(( 7 == $parno ) && !isset( $inputa['tz'] ))
            $inputa['tz'] = 'Z';
        }
      }
      elseif( 8 <= strlen( trim( $theRdate ))) { // ex. 2006-08-03 10:12:18
        $inputa       = $this->_date_time_string( $theRdate, $parno );
        $inpute['tz'] = ( isset( $input['params']['TZID'] )) ? $input['params']['TZID'] :
                        ( isset( $inpute['tz'] )) ? $inpute['tz'] : null;
        $parno = ( !isset( $parno )) ? count( $inputa ) : $parno;
        if(( 7 == $parno ) && !isset( $inputa['tz'] ))
          $inputa['tz'] = 'Z';
      }
      $input['value'][] = $inputa;
    }
    if( 0 < count( $input['value'] )) {
      if( 3 == $parno ) {
        $input['params']['VALUE'] = 'DATE';
        foreach( $input['value'] as $eix => $inputa )
          unset( $input['value'][$eix]['tz'] );
        unset( $input['params']['TZID'] );
      }
      if( !empty( $input['value'][0]['tz'] )   &&
           ( $input['value'][0]['tz'] != 'Z' ) &&
        ( !( in_array($input['value'][0]['tz']{0}, array( '+', '-' )) &&
             ctype_digit( substr( $input['value'][0]['tz'], 1 ))) &&
          !ctype_digit( $input['value'][0]['tz'] ) ) ) {
        $input['params']['TZID'] = $input['value'][0]['tz'];
        foreach( $input['value'] as $eix => $inputa ) {
          if( !empty( $input['value'][0]['tz'] )   &&
               ( $input['value'][0]['tz'] != 'Z' ) &&
            ( !( in_array( $input['value'][0]['tz']{0}, array( '+', '-' )) &&
                 ctype_digit( substr( $input['value'][0]['tz'], 1 ))) &&
              !ctype_digit( $input['value'][0]['tz'] ) ) )
            unset( $input['value'][$eix]['tz'] );
        }
      }
      elseif( isset( $input['params']['TZID'] ))
        unset( $input['params']['TZID'] );

     $this->rdate[] = $input;
    }
   //  echo 'setRdate ut '; print_r ( $this->rdate ); echo "<br />\n"; // test ##
  }
/*********************************************************************************/
/**
 * Property Name: RECURRENCE-ID
 */
/**
 * creates formatted output for calendar component property recurrence-id
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.18 - 2007-03-26
 * @return string
 */
  function createRecurrenceid() {
    $cnt = count( $this->recurrenceid );
    if( 0 >= $cnt )
      return;
    $formatted  = $this->_format_date_time( $this->recurrenceid['value'] );
    $attributes = $this->_createParams( $this->recurrenceid['params'] );
    return $this->_createElement( 'RECURRENCE-ID', $attributes, $formatted );
  }
/**
 * set calendar component property recurrence-id
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.18 - 2007-03-26
 * @param mixed $year
 * @param mixed $month optional
 * @param int $day optional
 * @param int $hour optional
 * @param int $min optional
 * @param int $sec optional
 * @param array $params optional
 * @return void
 */
  function setRecurrenceid( $year, $month=FALSE, $day=FALSE, $hour=FALSE, $min=FALSE, $sec=FALSE, $tz=FALSE, $params=FALSE ) {
    $parno = 0;
    if( is_array( $year ) && ( in_array( count( $year ), array( 3, 4, 6, 7 )))) {
      $this->recurrenceid['params'] = $this->_setParams( $month, array( 'VALUE' => 'DATE-TIME' ));
      if( isset( $this->recurrenceid['params']['TZID'] ))
        $year['tz'] = $this->recurrenceid['params']['TZID'];
      $hitval = ( !empty( $year['tz'] ) || !empty( $year[6] ) || ( 4 == count( $year ))) ? 7 : 6;
      $parno = $this->_existRem( $this->recurrenceid['params'], 'VALUE', 'DATE-TIME', $hitval );
      if( !isset( $parno )) $parno  = $this->_existRem( $this->recurrenceid['params'], 'VALUE', 'DATE', 3 );
      if( !isset( $parno )) $parno  = count( $year );
      $this->recurrenceid['value']  = $this->_date_time_array( $year, $parno );
    }
    elseif( is_array( $year ) && isset( $year['timestamp'] )) {
      $this->recurrenceid['params'] = $this->_setParams( $month, array( 'VALUE' => 'DATE-TIME' ));
      $tz    = ( isset( $year['tz'] )) ? ' '.$year['tz'] : null;
      $tz    = ( isset( $this->recurrenceid['params']['TZID'] ))
                  ? ' '.$this->recurrenceid['params']['TZID'] : $tz;
      $hitval = ( !empty( $tz )) ? 7 : 6;
      $parno = $this->_existRem( $this->recurrenceid['params'], 'VALUE', 'DATE-TIME', $hitval );
      if( !isset( $parno )) $parno  = $this->_existRem( $this->recurrenceid['params'], 'VALUE', 'DATE', 3 );
      if( !isset( $parno )) $parno  = $hitval;
      $this->recurrenceid['value']  = $this->_date_time_string( date( 'Y-m-d H:i:s', $year['timestamp'] ).$tz, $parno );
    }
    elseif( 8 <= strlen( trim( $year ))) { // ex. 2006-08-03 10:12:18
      $this->recurrenceid['params'] = $this->_setParams( $month, array( 'VALUE' => 'DATE-TIME' ));
      $parno = $this->_existRem( $this->recurrenceid['params'], 'VALUE', 'DATE-TIME', 7 );
      if( !isset( $parno )) $parno  = $this->_existRem( $this->recurrenceid['params'], 'VALUE', 'DATE', 3 );
      $this->recurrenceid['value']  = $this->_date_time_string( $year, $parno );
      if( isset( $this->recurrenceid['params']['TZID'] ))
        $this->recurrenceid['value']['tz'] = $this->recurrenceid['params']['TZID'];
    }
    else {
      $this->recurrenceid['params'] = $this->_setParams( $params, array( 'VALUE' => 'DATE-TIME' ));
      $tz     = ( isset( $this->recurrenceid['params']['TZID'] ))
                       ? $this->recurrenceid['params']['TZID'] : ( isset( $tz )) ? $tz : null;
      $hitval = ( !empty( $tz )) ? 7 : null;
      $parno  = $this->_existRem( $this->recurrenceid['params'], 'VALUE', 'DATE-TIME', $hitval );
      if( !isset( $parno )) $parno  = $this->_existRem( $this->recurrenceid['params'], 'VALUE', 'DATE', 3 );
      $this->recurrenceid['value']  = array( 'year'  => $year, 'month' => $month, 'day' => $day );
      if( 3 != $parno ) {
        $this->recurrenceid['value']['hour'] = ( $hour ) ? $hour : '0';
        $this->recurrenceid['value']['min']  = ( $min )  ? $min  : '0';
        $this->recurrenceid['value']['sec']  = ( $sec )  ? $sec  : '0';
        if( !empty( $tz ))
          $this->recurrenceid['value']['tz'] = $tz;
      }
    }
    if( 3 == $parno ) {
      $this->recurrenceid['params']['VALUE'] = 'DATE';
      unset( $this->recurrenceid['value']['tz'] );
      unset( $this->recurrenceid['params']['TZID'] );
    }
    if( !empty( $this->recurrenceid['value']['tz'] )   &&
         ( $this->recurrenceid['value']['tz'] != 'Z' ) &&
      ( !( in_array($this->recurrenceid['value']['tz']{0}, array( '+', '-' )) &&
           ctype_digit( substr( $this->recurrenceid['value']['tz'], 1 ))) &&
        !ctype_digit( $this->recurrenceid['value']['tz'] ) ) ) {
      $this->recurrenceid['params']['TZID'] = $this->recurrenceid['value']['tz'];
      unset( $this->recurrenceid['value']['tz'] );
    }
    elseif( isset( $this->recurrenceid['params']['TZID'] ))
      unset( $this->recurrenceid['params']['TZID'] );
  }
/*********************************************************************************/
/**
 * Property Name: RELATED-TO
 */
/**
 * creates formatted output for calendar component property related-to
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 1.x.x - 2007-05-24
 * @return string
 */
  function createRelatedTo() {
    $cnt = count( $this->relatedto );
    if( 0 >= $cnt )
      return;
    $output = null;
    foreach( $this->relatedto as $relation ) {
      $attributes = $this->_createParams( $relation['params'] );
      $content    = '<'.$relation['value'].'>';
      $output    .= $this->_createElement( 'RELATED-TO', $attributes, $content );
    }
    return $output;
  }
/**
 * set calendar component property related-to
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 1.x.x - 2007-05-24
 * @param float $relid
 * @param array $params optional
 * @return void
 */
  function setRelatedTo( $relid, $params=FALSE ) {
    $relation           = array();
    if(( '<' == substr( $relid, 0, 1 )) &&
       ( '>' == substr( $relid, -1 )))
                 $relid = substr( $relid, 1, ( strlen( $relid ) - 2 ));
    $relation['value']  = $relid;
    $relation['params'] = $this->_setParams( $params );
            // remove default
    if( isset( $relation['params']['RELTYPE'] ) &&
       ( strtoupper( $relation['params']['RELTYPE'] ) == 'PARENT' ))
      unset( $relation['params']['RELTYPE'] );
    $this->relatedto[]  = $relation;
  }
/*********************************************************************************/
/**
 * Property Name: REPEAT
 */
/**
 * creates formatted output for calendar component property repeat
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.7 - 2006-11-20
 * @return string
 */
  function createRepeat() {
    $cnt = count( $this->repeat );
    if( 0 >= $cnt )
      return;
    $attributes = $this->_createParams( $this->repeat['params'] );
    return $this->_createElement( 'REPEAT', $attributes, $this->repeat['value'] );
  }
/**
 * set calendar component property transp
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.18 - 2007-03-18
 * @param string $value
 * @param array $params optional
 * @return void
 */
  function setRepeat( $value, $params=FALSE ) {
    $this->repeat['value']  = $value;
    $this->repeat['params'] = $this->_setParams( $params );
  }
/*********************************************************************************/
/**
 * Property Name: REQUEST-STATUS
 */
/**
 * creates formatted output for calendar component property request-status
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 1.x.x - 2007-05-16
 * @return string
 */
  function createRequestStatus() {
    $cnt = count( $this->requeststatus );
    if( 0 >= $cnt )
      return;
    $output = null;
    foreach( $this->requeststatus as $rstat ) {
      $attributes = $this->_createParams( $rstat['params'], array( 'LANGUAGE' ));
      $content    = number_format( (float) $rstat['value']['statcode'], 2, '.', '');
      $content   .= ';'.$this->_strrep( $rstat['value']['text'] );
      if( isset( $rstat['value']['extdata'] ))
        $content .= ';'.$this->_strrep( $rstat['value']['extdata'] );
      $output    .= $this->_createElement( 'REQUEST-STATUS', $attributes, $content );
    }
    return $output;
  }
/**
 * set calendar component property request-status
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 1.x.x - 2007-05-16
 * @param float $statcode
 * @param string $text
 * @param string $extdata optional
 * @param array params optional
 * @return void
 */
  function setRequestStatus( $statcode, $text, $extdata=FALSE, $params=FALSE ) {
    $input = array();
    $input['value']['statcode']  = $statcode;
    $input['value']['text']      = $text;
    if( $extdata )
      $input['value']['extdata'] = $extdata;
    $input['params']    = $this->_setParams( $params);
    $this->requeststatus[] = $input;
  }
/*********************************************************************************/
/**
 * Property Name: RESOURCES
 */
/**
 * creates formatted output for calendar component property resources
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 1.x.x - 2007-05-19
 * @return string
 */
  function createResources() {
    if( 0 >= count( $this->resources ))
      return;
    $output = null;
    foreach( $this->resources as $resource ) {
      $attributes = $this->_createParams( $resource['params'], array( 'ALTREP', 'LANGUAGE' ));
      $output    .= $this->_createElement( 'RESOURCES', $attributes, $resource['value'] );
    }
    return $output;
  }
/**
 * set calendar component property recources
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 1.x.x - 2007-05-19
 * @param mixed $value
 * @param array params optional
 * @return void
 */
  function setResources( $value, $params=FALSE ) {
    $input = array();
    $value = ( is_array( $value )) ? implode( ',', $value ) : $value;
    $value = str_replace( ', ', ',', $value );
    $value = str_replace( ' ,', ',', $value );
    $input['value']    = $value;
    $input['params']   = $this->_setParams( $params );
    $this->resources[] = $input;
  }
/*********************************************************************************/
/**
 * Property Name: RRULE
 */
/**
 * creates formatted output for calendar component property rrule
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.7.43 - 2006-09-15
 * @return string
 */
  function createRrule() {
    $cnt = count( $this->rrule );
    if( 0 >= $cnt )
      return;
    return $this->_format_recur( 'RRULE', $this->rrule );
  }
/**
 * set calendar component property rrule
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.18 - 2007-03-18
 * @param array $rruleset
 * @param array $params optional
 * @return void
 */
  function setRrule( $rruleset, $params=FALSE ) {
    $exrule = array();
    foreach( $rruleset as $rrulelabel => $rrulevalue ) {
      $rrulelabel = strtoupper( $rrulelabel );
      if( 'UNTIL'  != $rrulelabel )
        $rrule['value'][$rrulelabel] = $rrulevalue;
      elseif( is_array( $rrulevalue ) &&
            (( 3 == count( $rrulevalue )) ||
             ( 6 == count( $rrulevalue )) ||
             ( 7 == count( $rrulevalue )) ||
             ( array_key_exists( 'year', $rrulevalue )))) {
        $parno = ( 3 < count( $rrulevalue )) ? 7 : 3 ; // datetime / date
        $date  = $this->_date_time_array( $rrulevalue, $parno );
        if(( 3 < count( $date )) && !isset( $date['tz'] ))
          $date['tz'] = 'Z';
        $rrule['value'][$rrulelabel] = $date;
      }
      elseif( is_array( $rrulevalue ) && isset( $rrulevalue['timestamp'] )) {
        $date  = $this->_date_time_string( date( 'Y-m-d H:i:s', $rrulevalue['timestamp'] ), 6 );
        $date['tz'] = 'Z';
        $rrule['value'][$rrulelabel] = $date;
      }
      elseif( 8 <= strlen( trim( $rrulevalue ))) { // ex. 2006-08-03 10:12:18
        $date = $this->_date_time_string( $rrulevalue );
        if(( 3 < count( $date )) && !isset( $date['tz'] ))
          $date['tz'] = 'Z';
        $rrule['value'][$rrulelabel] = $date;
      }
    }
    $rrule['params'] = $this->_setParams( $params );
    $this->rrule[]   = $rrule;
  }
/*********************************************************************************/
/**
 * Property Name: SEQUENCE
 */
/**
 * creates formatted output for calendar component property sequence
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.7 - 2006-11-20
 * @return string
 */
  function createSequence() {
    $cnt = count( $this->sequence );
    if( 0 >= $cnt )
      return;
    $attributes = $this->_createParams( $this->sequence['params'] );
    return $this->_createElement( 'SEQUENCE', $attributes, $this->sequence['value'] );
  }
/**
 * set calendar component property sequence
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 1.x.x - 2007-04-25
 * @param int $value optional
 * @param array $params optional
 * @return void
 */
  function setSequence( $value=FALSE, $params=FALSE ) {
    if( !$value )
      $value = $this->getProperty( 'sequence' ) + 1;
    $this->sequence['value']  = $value;
    $this->sequence['params'] = $this->_setParams( $params );
  }
/*********************************************************************************/
/**
 * Property Name: STATUS
 */
/**
 * creates formatted output for calendar component property status
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.7 - 2006-11-20
 * @return string
 */
  function createStatus() {
    $cnt = count( $this->status );
    if( 0 >= $cnt )
      return;
    $attributes = $this->_createParams( $this->status['params'] );
    return $this->_createElement( 'STATUS', $attributes, $this->status['value'] );
  }
/**
 * set calendar component property status
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.18 - 2007-03-18
 * @param string $value
 * @param array $params optional
 * @return void
 */
  function setStatus( $value, $params=FALSE ) {
    $this->status['value']  = $value;
    $this->status['params'] = $this->_setParams( $params );
  }
/*********************************************************************************/
/**
 * Property Name: SUMMARY
 */
/**
 * creates formatted output for calendar component property summary
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.7 - 2006-11-20
 * @return string
 */
  function createSummary() {
    $cnt = count( $this->summary );
    if( 0 >= $cnt )
      return;
    $attributes = $this->_createParams( $this->summary['params'], array( 'ALTREP', 'LANGUAGE' ));
    $content    = $this->_strrep( $this->summary['value'] );
    return $this->_createElement( 'SUMMARY', $attributes, $content );
  }
/**
 * set calendar component property summary
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.18 - 2007-03-18
 * @param string $value
 * @param string $params optional
 * @return void
 */
  function setSummary( $value, $params=FALSE ) {
    $this->summary['value']  = $value;
    $this->summary['params'] = $this->_setParams( $params );
  }
/*********************************************************************************/
/**
 * Property Name: TRANSP
 */
/**
 * creates formatted output for calendar component property transp
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.7 - 2006-11-20
 * @return string
 */
  function createTransp() {
    $cnt = count( $this->transp );
    if( 0 >= $cnt )
      return;
    $attributes = $this->_createParams( $this->transp['params'] );
    return $this->_createElement( 'TRANSP', $attributes, $this->transp['value'] );
  }
/**
 * set calendar component property transp
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.18 - 2007-03-18
 * @param string $value
 * @param string $params optional
 * @return void
 */
  function setTransp( $value, $params=FALSE ) {
    $this->transp['value']  = $value;
    $this->transp['params'] = $this->_setParams( $params );
  }
/*********************************************************************************/
/**
 * Property Name: TRIGGER
 */
/**
 * creates formatted output for calendar component property trigger
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.19 - 2007-03-27
 * @return string
 */
  function createTrigger() {
    $cnt = count( $this->trigger );
    if( 0 >= $cnt )
      return;
    $content = $attributes = null;
    if( isset( $this->trigger['value']['year'] )   &&
        isset( $this->trigger['value']['month'] )  &&
        isset( $this->trigger['value']['day'] ))
      $content      .= $this->_format_date_time( $this->trigger['value'] );
    else {
      if( TRUE !== $this->trigger['value']['relatedstart'] )
        $attributes .= $this->intAttrDelimiter.'RELATED=END';
      if( $this->trigger['value']['before'] )
        $content    .= '-';
      $content      .= $this->_format_duration( $this->trigger['value'] );
    }
    $attributes     .= $this->_createParams( $this->trigger['params'] );
    return $this->_createElement( 'TRIGGER', $attributes, $content );
  }
/**
 * set calendar component property trigger
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 1.x.x - 2007-05-01
 * @param mixed $year
 * @param mixed $month optional
 * @param int $day optional
 * @param int $week optional
 * @param int $hour optional
 * @param int $min optional
 * @param int $sec optional
 * @param bool $relatedEnd optional
 * @param bool $after optional
 * @param string $tz optional
 * @param array $params optional
 * @return void
 */
  function setTrigger( $year=FALSE, $month=FALSE, $day=FALSE, $week=FALSE, $hour=FALSE, $min=FALSE, $sec=FALSE, $relatedEnd=FALSE, $after=FALSE, $tz=FALSE, $params=FALSE ) {
    if( is_array( $year ) && array_key_exists( 'timestamp', $year )) { // timestamp
      $params = $this->_setParams( $month );
      $this->_existRem( $params, 'VALUE', 'DATE-TIME' );
      if( isset( $params['TZID'] )) {
        $year['tz'] = $params['TZID'];
        unset( $params['TZID'] );
      }
      $tz     = ( isset( $year['tz'] )) ? ' '.$year['tz'] : null;
      $parno  = ( isset( $tz )) ? 7 : 6;
      $date   = $this->_date_time_string( date( 'Y-m-d H:i:s', $year['timestamp'] ).$tz, $parno );
      foreach( $date as $k => $v )
        $$k = $v;
    }
    elseif( is_array( $year )) {
      if( array_key_exists( 'year',  $year ) &&
          array_key_exists( 'month', $year ) &&
          array_key_exists( 'day',   $year )) {  // date-time
        $params = $this->_setParams( $month );
        if( isset( $params['TZID'] )) {
          $year['tz'] = $params['TZID'];
          unset( $params['TZID'] );
        }
      }
      else { // duration
        $relatedEnd = $month;
        $after      = $day;
        $params = $this->_setParams( $week );
      }
      $this->_existRem( $params, 'VALUE', 'DATE-TIME' );
      $this->_existRem( $params, 'VALUE', 'DURATION' );
      $SSYY  = ( array_key_exists( 'year',  $year )) ? $year['year']  : null;
      $month = ( array_key_exists( 'month', $year )) ? $year['month'] : null;
      $day   = ( array_key_exists( 'day',   $year )) ? $year['day']   : null;
      $week  = ( array_key_exists( 'week',  $year )) ? $year['week']  : null;
      $hour  = ( array_key_exists( 'hour',  $year )) ? $year['hour']  : null;
      $min   = ( array_key_exists( 'min',   $year )) ? $year['min']   : null;
      $sec   = ( array_key_exists( 'sec',   $year )) ? $year['sec']   : null;
      $tz    = ( array_key_exists( 'tz',    $year )) ? $year['tz']    : null;
      $year  = $SSYY;
    }
    elseif( is_string($year) && !is_int( $year )) {  // duration or date in a string
      $params = $this->_setParams( $month );
      unset( $month );
      $this->_existRem( $params, 'VALUE', 'DATE-TIME' );   // ??
      $this->_existRem( $params, 'VALUE', 'DURATION' );
      if( in_array( $year{0}, array( 'P', '+', '-' ))) { // duration
        if( '-' == $year{0} )
          $after = FALSE;
        elseif( '+' == $year{0} )
          $after = TRUE;
        elseif( 'P' == $year{0} )
          $after = TRUE;
        if( 'P' != $year{0} )
          $year  = substr( $year, 1 );
        $date    = $this->_duration_string( $year);
      }
      else {
        $date    = $this->_date_time_string( $year, 7 );   // date
        if( isset( $params['TZID'] )) {
          $date['tz'] = $params['TZID'];
          unset( $params['TZID'] );
        }
      }
      foreach( $date as $k => $v )
        $$k = $v;
    }
    else
      $params = $this->_setParams( $params );
    if( !empty( $year ) && !empty( $month ) && !empty( $day ) ) { // date
      $params['VALUE'] = 'DATE-TIME';
      $this->trigger['value'] = array( 'year'  => $year
                                     , 'month' => $month
                                     , 'day'   => $day);
      $this->trigger['value']['hour']   = $hour;
      $this->trigger['value']['min']    = $min;
      $this->trigger['value']['sec']    = $sec;
      if( !empty( $tz ))
        $this->trigger['value']['tz']   = $tz;
      else
        $this->trigger['value']['tz']   = 'Z';
    }
    else {                                                   // duration
      $this->_existRem( $params, 'VALUE', 'DURATION' );
      if( $this->_existRem( $params, 'RELATED', 'END', TRUE ))
        $relatedEnd = TRUE;
      elseif( $this->_existRem( $params, 'RELATED', 'START', TRUE ))
        $relatedEnd = FALSE;
      if( !empty( $week )) {
        $this->trigger['value'] = array( 'week'         => $week
                                       , 'relatedstart' => !$relatedEnd
                                       , 'before'       => !$after );
      }
      else {
        $this->trigger['value'] = array( 'day'          => $day
                                       , 'hour'         => $hour
                                       , 'min'          => $min
                                       , 'sec'          => $sec
                                       , 'relatedstart' => !$relatedEnd
                                       , 'before'       => !$after );
      }
    }
    if( !isset( $this->trigger['value'] ))
      return;
    $this->trigger['params'] = $params;
  }
/*********************************************************************************/
/**
 * Property Name: TZID
 */
/**
 * creates formatted output for calendar component property tzid
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.7 - 2006-11-20
 * @return string
 */
  function createTzid() {
    $cnt = count( $this->tzid );
    if( 0 >= $cnt )
      return;
    $attributes = $this->_createParams( $this->tzid['params'] );
    return $this->_createElement( 'TZID', $attributes, $this->tzid['value'] );
  }
/**
 * set calendar component property tzid
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.18 - 2007-03-18
 * @param string $value
 * @param array $params optional
 * @return void
 */
  function setTzid( $value, $params=FALSE ) {
    $this->tzid['value']   = $value;
    if( !isset( $this->tzid['value'] ))
      return;
    $this->tzid['params'] = $this->_setParams( $params );
  }
/*********************************************************************************/
/**
 * .. .
 * Property Name: TZNAME
 */
/**
 * creates formatted output for calendar component property tzname
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 1.x.x - 2007-05-24
 * @return string
 */
  function createTzname() {
    $cnt = count( $this->tzname );
    if( 0 >= $cnt )
      return;
    $output = null;
    foreach( $this->tzname as $theName ) {
      $attributes = $this->_createParams( $theName['params'], array( 'LANGUAGE' ));
      $output    .= $this->_createElement( 'TZNAME', $attributes, $theName['value'] );
    }
    return $output;
  }
/**
 * set calendar component property tzname
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.18 - 2007-03-18
 * @param string $value
 * @param string $params optional
 * @return void
 */
  function setTzname( $value, $params=FALSE ) {
    $input['value']  = $value;
    if( empty( $input['value'] ))
      return;
    $input['params'] = $this->_setParams( $params );
    $this->tzname[] = $input;
  }
/*********************************************************************************/
/**
 * Property Name: TZOFFSETFROM
 */
/**
 * creates formatted output for calendar component property tzoffsetfrom
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.7 - 2006-11-20
 * @return string
 */
  function createTzoffsetfrom() {
    $cnt = count( $this->tzoffsetfrom );
    if( 0 >= $cnt )
      return;
    $attributes = $this->_createParams( $this->tzoffsetfrom['params'] );
    return $this->_createElement( 'TZOFFSETFROM', $attributes, $this->tzoffsetfrom['value'] );
  }
/**
 * set calendar component property tzoffsetfrom
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 1.x.x - 2007-05-25
 * @param string $value
 * @param string $params optional
 * @return void
 */
  function setTzoffsetfrom( $value, $params=FALSE ) {
    $this->tzoffsetfrom['value']   = $value;
    if( empty( $this->tzoffsetfrom['value'] ))
      return;
    $this->tzoffsetfrom['params'] = $this->_setParams( $params );
  }
/*********************************************************************************/
/**
 * Property Name: TZOFFSETTO
 */
/**
 * creates formatted output for calendar component property tzoffsetto
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.7 - 2006-11-20
 * @return string
 */
  function createTzoffsetto() {
    $cnt = count( $this->tzoffsetto );
    if( 0 >= $cnt )
      return;
    $attributes = $this->_createParams( $this->tzoffsetto['params'] );
    return $this->_createElement( 'TZOFFSETTO', $attributes, $this->tzoffsetto['value'] );
  }
/**
 * set calendar component property tzoffsetto
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.18 - 2007-03-18
 * @param string $value
 * @param string $params optional
 * @return void
 */
  function setTzoffsetto( $value, $params=FALSE ) {
    $this->tzoffsetto['value']  = $value;
    if( empty( $this->tzoffsetto['value'] ))
      return;
    $this->tzoffsetto['params'] = $this->_setParams( $params );
  }
/*********************************************************************************/
/**
 * Property Name: TZURL
 */
/**
 * creates formatted output for calendar component property tzurl
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.7 - 2006-11-20
 * @return string
 */
  function createTzurl() {
    $cnt = count( $this->tzurl );
    if( 0 >= $cnt )
      return;
    $attributes = $this->_createParams( $this->tzurl['params'] );
    return $this->_createElement( 'TZURL', $attributes, $this->tzurl['value'] );
  }
/**
 * set calendar component property tzurl
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.18 - 2007-03-18
 * @param string $value
 * @param string $params optional
 * @return void
 */
  function setTzurl( $value, $params=FALSE ) {
    $this->tzurl['value']   = $value;
    if( empty( $this->tzurl['value'] ))
      return;
    $this->tzurl['params'] = $this->_setParams( $params );
  }
/*********************************************************************************/
/**
 * Property Name: UID
 */
/**
 * creates formatted output for calendar component property uid
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.7 - 2006-11-20
 * @return string
 */
  function createUid() {
    $cnt = count( $this->uid );
    if( 0 >= $cnt ) {
      $this->_makeuid();
    }
    $attributes = $this->_createParams( $this->uid['params'] );
    return $this->_createElement( 'UID', $attributes, $this->uid['value'] );
  }
/**
 * create an unique id for this calendar component object instance
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 1.x.x - 2007-03-18
 * @return void
 */
  function _makeUid() {
    $unique = null;
    $base   = 'aAbBcCdDeEfFgGhHiIjJkKlLmMnNoOpPrRsStTuUvVxXuUvVwWzZ1234567890';
    $start  = 0;
    $end    = strlen( $base ) - 1;
    $length = 10;
    $str    = null;
    for( $p = 0; $p < $length; $p++ ) {
      $basePos = mt_rand( $start, $end );
      $unique .= $base{$basePos};
    }
    $this->uid['value']  = date('Ymd\THisT').'-'.$unique.'@'.$this->getConfig( 'unique_id' );
    $this->uid['params'] = null;
  }
/**
 * set calendar component property uid
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.18 - 2007-03-18
 * @param string $value
 * @param string $params optional
 * @return void
 */
  function setUid( $value, $params=FALSE ) {
    $this->uid['value']  = $value;
    if( empty( $this->uid['value'] ))
      return;
    $this->uid['params'] = $this->_setParams( $params );
  }
/*********************************************************************************/
/**
 * Property Name: URL
 */
/**
 * creates formatted output for calendar component property url
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.7 - 2006-11-20
 * @return string
 */
  function createUrl() {
    $cnt = count( $this->url );
    if( 0 >= $cnt )
      return;
    $attributes = $this->_createParams( $this->url['params'] );
    return $this->_createElement( 'URL', $attributes, $this->url['value'] );
  }
/**
 * set calendar component property url
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.18 - 2007-03-18
 * @param string $value
 * @param string $params optional
 * @return void
 */
  function setUrl( $value, $params=FALSE ) {
    $this->url['value']   = $value;
    if( empty( $this->url['value'] ))
      return;
    $this->url['params'] = $this->_setParams( $params );
  }
/*********************************************************************************/
/**
 * Property Name: x-prop
 */
/**
 * creates formatted output for calendar component property x-prop
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.21 - 2007-03-18
 * @return string
 */
  function createXprop() {
    if( 0 >= count( $this->xprop ))
      return;
    $xprop = null;
    foreach( $this->xprop as $xpropPart ) {
      $attributes = $this->_createParams( $xpropPart['params'], array( 'LANGUAGE' ));
      foreach( $xpropPart['value'] as $label => $value ) {
        if( is_array( $value ))
          $value  = implode( ',', $value );
        $xprop   .= $this->_createElement( $label, $attributes, $value );
      }
    }
    return $xprop;
  }
/**
 * set calendar component property x-prop
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.21 - 2007-04-04
 * @param string $label
 * @param mixed $value
 * @param array $params optional
 * @return void
 */
  function setXprop( $label, $value, $params=FALSE ) {
    $xprop['value']  = array( $label => $value);
    if( empty( $xprop['value'][$label] ))
      return;
    $xprop['params'] = $this->_setParams( $params );
    $this->xprop[]   = $xprop;
  }
/*********************************************************************************/
/*********************************************************************************/
/**
 * create element format parts
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.7 - 2006-11-20
 * @return string
 */
  function _createFormat() {
    switch( $this->format ) {
      case 'xcal':
        $this->objName            = ( isset( $this->timezonetype )) ?
                                 strtolower( $this->timezonetype )  :  strtolower( $this->objName );
        $this->componentStart1    = $this->elementStart1 = '<';
        $this->componentStart2    = $this->elementStart2 = '>';
        $this->componentEnd1      = $this->elementEnd1   = '</';
        $this->componentEnd2      = $this->elementEnd2   = '>'.$this->nl;
        $this->intAttrDelimiter   = '<!-- -->';
        $this->attributeDelimiter = $this->nl;
        $this->valueInit          = null;
        break;
      default:
        $this->objName            = ( isset( $this->timezonetype )) ?
                                 strtoupper( $this->timezonetype )  :  strtoupper( $this->objName );
        $this->componentStart1    = 'BEGIN:';
        $this->componentStart2    = null;
        $this->componentEnd1      = 'END:';
        $this->componentEnd2      = $this->nl;
        $this->elementStart1      = null;
        $this->elementStart2      = null;
        $this->elementEnd1        = null;
        $this->elementEnd2        = $this->nl;
        $this->intAttrDelimiter   = '<!-- -->';
        $this->attributeDelimiter = ';';
        $this->valueInit          = ':';
        break;
    }
  }
/**
 * creates formatted output for calendar component property
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.15 - 2007-01-08
 * @param string $label property name
 * @param string $attributes property attributes
 * @param string $content property content (optional)
 * @return string
 */
  function _createElement( $label, $attributes, $content=FALSE ) {
    $label  = $this->_formatPropertyName( $label );
    $output = $this->elementStart1.$label;
    $categoriesAttrLang = null;
    $attachInlineBinary = FALSE;
    $attachfmttype      = null;
    if( !empty( $attributes ))  {
      $attributes  = trim( $attributes );
      if ( 'xcal' == $this->format) {
        $attributes2 = explode( $this->intAttrDelimiter, $attributes );
        $attributes  = null;
        foreach( $attributes2 as $attribute ) {
          $attrKVarr = explode( '=', $attribute );
          if( empty( $attrKVarr[0] ))
            continue;
          if( !isset( $attrKVarr[1] )) {
            $attrValue = $attrKVarr[0];
            $attrKey   = null;
          }
          elseif( 2 == count( $attrKVarr)) {
            $attrKey   = strtolower( $attrKVarr[0] );
            $attrValue = $attrKVarr[1];
          }
          else {
            $attrKey   = strtolower( $attrKVarr[0] );
            unset( $attrKVarr[0] );
            $attrValue = implode( '=', $attrKVarr );
          }
          if(( 'attach' == $label ) && ( in_array( $attrKey, array( 'fmttype', 'encoding', 'value' )))) {
            $attachInlineBinary = TRUE;
            if( 'fmttype' == $attrKey )
              $attachfmttype = $attrKey.'='.$attrValue;
            continue;
          }
          elseif(( 'categories' == $label ) && ( 'language' == $attrKey ))
            $categoriesAttrLang = $attrKey.'='.$attrValue;
          else {
            $attributes .= ( empty( $attributes )) ? ' ' : $this->attributeDelimiter.' ';
            $attributes .= ( !empty( $attrKey )) ? $attrKey.'=' : null;
            if(( '"' == substr( $attrValue, 0, 1 )) && ( '"' == substr( $attrValue, -1 ))) {
              $attrValue = substr( $attrValue, 1, ( strlen( $attrValue ) - 2 ));
              $attrValue = str_replace( '"', '', $attrValue );
            }
            $attributes .= '"'.htmlspecialchars( $attrValue ).'"';
          }
        }
      }
      else {
        $attributes = str_replace( $this->intAttrDelimiter, $this->attributeDelimiter, $attributes );
      }
    }
    if(((( 'attach' == $label ) && !$attachInlineBinary ) ||
         ( in_array( $label, array( 'tzurl', 'url' ))))      && ( 'xcal' == $this->format)) {
      $pos = strrpos($content, "/");
      $docname = ( $pos !== false) ? substr( $content, (1 - strlen( $content ) + $pos )) : $content;
      $this->xcaldecl[] = array( 'xmldecl'  => 'ENTITY'
                               , 'uri'      => $docname
                               , 'ref'      => 'SYSTEM'
                               , 'external' => $content
                               , 'type'     => 'NDATA'
                               , 'type2'    => 'BINERY' );
      $attributes .= ( empty( $attributes )) ? ' ' : $this->attributeDelimiter.' ';
      $attributes .= 'uri="'.$docname.'"';
      $content = null;
      if( 'attach' == $label ) {
        $attributes = str_replace( $this->attributeDelimiter, $this->intAttrDelimiter, $attributes );
        $content = $this->_createElement( 'extref', $attributes, null );
        $attributes = null;
      }
    }
    elseif(( 'attach' == $label ) && $attachInlineBinary && ( 'xcal' == $this->format)) {
      $content = $this->nl.$this->_createElement( 'b64bin', $attachfmttype, $content ); // max one attribute
    }
    $output .= $attributes;
    if( !$content ) {
      switch( $this->format ) {
        case 'xcal':
          $output .= ' /';
          $output .= $this->elementStart2;
          return $output;
          break;
        default:
          $output .= $this->elementStart2;
          return $this->_size75( $output );
          break;
      }
    }
    $output .= $this->elementStart2;
    switch( $label ) {
      case 'categories':
 //   case 'resources': ??
        $output  .= $this->nl;
        $items    = explode(',', $content);
        $content  = null;
        foreach( $items as $item )
          $content .= $this->_createElement( 'item', $categoriesAttrLang, $item );  // max one attribute
        break;
      case 'geo':
        $output  .= $this->nl;
        list($lat, $lon) = explode(';', $content);
        $content  = null;
        $content .= $this->_createElement( 'lat', null, $lat );
        $content .= $this->_createElement( 'lon', null, $lon );
        break;
      default:
        break;
    }
    $output .= $this->valueInit.$content;
    switch( $this->format ) {
      case 'xcal':
        return $output.$this->elementEnd1.$label.$this->elementEnd2;
        break;
      default:
        return $this->_size75( $output );
        break;
    }
  }
/**
 * creates formatted output for calendar component property parameters
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.22 - 2007-04-10
 * @param array $params  optional
 * @param array $ctrKeys optional
 * @return string
 */
  function _createParams( $params=array(), $ctrKeys=array() ) {
    $attrLANG = $attr1 = $attr2 = null;
    $CNattrKey   = ( in_array( 'CN',       $ctrKeys )) ? TRUE : FALSE ;
    $LANGattrKey = ( in_array( 'LANGUAGE', $ctrKeys )) ? TRUE : FALSE ;
    $CNattrExist = $LANGattrExist = FALSE;
    if( is_array( $params )) {
      foreach( $params as $paramKey => $paramValue ) {
        if( is_int( $paramKey ))
          $attr2            .= $this->intAttrDelimiter.$paramValue;
        elseif(( 'LANGUAGE' == $paramKey ) && $LANGattrKey ) {
          $attrLANG         .= $this->intAttrDelimiter."LANGUAGE=$paramValue";
          $LANGattrExist     = TRUE;
        }
        elseif(( 'CN'       == $paramKey ) && $CNattrKey ) {
          $attr1             = $this->intAttrDelimiter.'CN="'.$paramValue.'"';
          $CNattrExist       = TRUE;
        }
        elseif(( 'ALTREP'   == $paramKey ) && in_array( $paramKey, $ctrKeys ))
          $attr2            .= $this->intAttrDelimiter.'ALTREP="'.$paramValue.'"';
        elseif(( 'DIR'      == $paramKey ) && in_array( $paramKey, $ctrKeys ))
          $attr2            .= $this->intAttrDelimiter.'DIR="'.$paramValue.'"';
        elseif(( 'SENT-BY'  == $paramKey ) && in_array( $paramKey, $ctrKeys ))
          $attr2            .= $this->intAttrDelimiter.'SENT-BY="MAILTO:'.$paramValue.'"';
        else
          $attr2            .= $this->intAttrDelimiter."$paramKey=$paramValue";
      }
    }
    if( !$LANGattrExist ) {
      $lang = $this->getConfig( 'language' );
      if(( $CNattrExist || $LANGattrKey ) && $lang )
        $attrLANG .= $this->intAttrDelimiter.'LANGUAGE='.$lang;
    }
    return $attrLANG.$attr1.$attr2;
  }
/**
 * ensures internal date-time/date format for input date-time/date in array format
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-15
 * @param array $datetime
 * @param int $parno optional, default FALSE
 * @return array
 */
  function _date_time_array( $datetime, $parno=FALSE ) {
    $output = array();
    foreach( $datetime as $dateKey => $datePart ) {
      switch ( $dateKey ) {
        case '0': case 'year':   $output['year']  = $datePart; break;
        case '1': case 'month':  $output['month'] = $datePart; break;
        case '2': case 'day':    $output['day']   = $datePart; break;
      }
      if( 3 != $parno ) {
        switch ( $dateKey ) {
          case '0':
          case '1':
          case '2': break;
          case '3': case 'hour': $output['hour']  = $datePart; break;
          case '4': case 'min' : $output['min']   = $datePart; break;
          case '5': case 'sec' : $output['sec']   = $datePart; break;
          case '6': case 'tz'  : $output['tz']    = $datePart; break;
        }
      }
    }
    if( 3 != $parno ) {
      if( !isset( $output['hour'] ))
        $output['hour'] = 0;
      if( !isset( $output['min']  ))
        $output['min'] = 0;
      if( !isset( $output['sec']  ))
        $output['sec'] = 0;
    }
    return $output;
  }
/**
 * ensures internal date-time/date format for input date-time/date in string fromat
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.9 - 2006-11-28
 * @param array $datetime
 * @param int $parno optional, default FALSE
 * @return array
 */
  function _date_time_string( $datetime, $parno=FALSE ) {
    $datetime = trim( $datetime );
    $tz  = null;
    $len = strlen( $datetime ) - 1;
    if( 'Z' == substr( $datetime, -1 )) {
      $tz = 'Z';
      $datetime = trim( substr( $datetime, 0, $len ));
    }
    elseif( ( ctype_digit( substr( $datetime, -2, 2 ))) && // time or date
            ( '-' == substr( $datetime, -3, 1 )) ||
            ( ':' == substr( $datetime, -3, 1 )) ||
            ( '.' == substr( $datetime, -3, 1 ))) {
      // continue;
    }
    elseif( ( ctype_digit( substr( $datetime, -4, 4 ))) && // 4 pos offset
            ( ' +' == substr( $datetime, -6, 2 )) ||
            ( ' -' == substr( $datetime, -6, 2 ))) {
      $tz = substr( $datetime, -5, 5 );
      $datetime = substr( $datetime, 0, ($len - 5));
    }
    elseif( ( ctype_digit( substr( $datetime, -6, 6 ))) && // 6 pos offset
            ( ' +' == substr( $datetime, -8, 2 )) ||
            ( ' -' == substr( $datetime, -8, 2 ))) {
      $tz = substr( $datetime, -7, 7 );
      $datetime = substr( $datetime, 0, ($len - 7));
    }
    elseif( ( 6 < $len ) && ( ctype_digit( substr( $datetime, -6, 6 )))) {
      // continue;
    }
    elseif( 'T' ==  substr( $datetime, -7, 1 )) {
      // continue;
    }
    else {
      $cx  = $tx = 0;    //  19970415T133000 US-Eastern
      $prevchar = null;
      for( $cx = -1; $cx > ( 9 - $len ); $cx-- ) {
        if(( ctype_alpha( substr( $datetime, $cx, 1 ))) ||
           (       ( ctype_alpha( $prevchar )) &&                     // prev char
                ( '-' ==  substr( $datetime, $cx, 1 )) &&             // this char
           ( ctype_alpha( substr( $datetime, ( $cx - 1) , 1 ))) ) ) { // next char
          $tx++;
          $prevchar = substr( $datetime, $cx, 1 );
        }
        else
          break;
      }
      if( 0 < $tx ) {
        $tz = substr( $datetime, ( 0 - $tx  ));
        $datetime = trim( substr( $datetime, 0, $len - $tx + 1 ));
      }
    }
  //    echo "_date_time_string 1a:  $datetime tz=$tz.<br />\n"; // test ###
    if( 0 < substr_count( $datetime, '-' )) {
      $datetime = str_replace( '-', '/', $datetime );
  //    echo "_date_time_string 1b:  $datetime tz=$tz.<br />\n"; // test ###
    }
    $datestring = date( 'Y-m-d H:i:s', strtotime( $datetime ));
  //    echo "_date_time_string 2:  $datestring tz=$tz.<br />\n"; // test ###
    $output     = array();
    $output['year']    = substr( $datestring, 0, 4 );
    $output['month']   = substr( $datestring, 5, 2 );
    $output['day']     = substr( $datestring, 8, 2 );
    if(( 6 == $parno ) || ( 7 == $parno )) {
      $output['hour']  = substr( $datestring, 11, 2 );
      $output['min']   = substr( $datestring, 14, 2 );
      $output['sec']   = substr( $datestring, 17, 2 );
      if( !empty( $tz ))
        $output['tz']  = $tz;
    }
    elseif( 3 != $parno ) {
      if(( '00' < substr( $datestring, 11, 2 )) ||
         ( '00' < substr( $datestring, 14, 2 )) ||
         ( '00' < substr( $datestring, 17, 2 ))) {
        $output['hour']  = substr( $datestring, 11, 2 );
        $output['min']   = substr( $datestring, 14, 2 );
        $output['sec']   = substr( $datestring, 17, 2 );
      }
      if( !empty( $tz ))
        $output['tz']  = $tz;
    }
    return $output;
  }
/**
 * ensures internal duration format for input in array format
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-15
 * @param array $duration
 * @return array
 */
  function _duration_array( $duration ) {
    $output = array();
    foreach( $duration as $durKey => $durValue ) {
      switch ( $durKey ) {
        case '0': case 'week': $output['week']  = $durValue; break;
        case '1': case 'day':  $output['day']   = $durValue; break;
        case '2': case 'hour': $output['hour']  = $durValue; break;
        case '3': case 'min':  $output['min']   = $durValue; break;
        case '4': case 'sec':  $output['sec']   = $durValue; break;
      }
    }
    if( isset( $output['week'] ) && ( 0 < $output['week'] ))
      return $output;
    elseif (( isset( $output['hour'] ) && ( 0 < $output['hour'] )) ||
            ( isset( $output['min'] )  && ( 0 < $output['min']  )) ||
             (isset( $output['sec'] )  && ( 0 < $output['sec']  ))) {
      if( !isset( $output['hour'] ))
        $output['hour'] = 0;
      if( !isset( $output['min']  ))
        $output['min']  = 0;
      if( !isset( $output['sec']  ))
        $output['sec']  = 0;
    }
    return $output;
  }
/**
 * ensures internal duration format for input in string format
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.18 - 2007-03-14
 * @param string $duration
 * @return array
 */
  function _duration_string( $duration ) {
   $duration = trim( $duration );
   while( 'P' != strtoupper( substr( $duration, 0, 1 ))) {
     if( 0 < strlen( $duration ))
       $duration = substr( $duration, 1 );
     else
       return false; // no leading P !?!?
   }
   $duration = substr( $duration, 1 ); // skip P
   $duration = str_replace ( 't', 'T', $duration );
   $duration = str_replace ( 'T', '', $duration );
   $output = array();
   $val    = null;
   for( $ix=0; $ix < strlen( $duration ); $ix++ ) {
     switch( strtoupper( $duration{$ix} )) {
      case 'W':
        $output['week'] = $val;
        $val            = null;
        break;
      case 'D':
        $output['day']  = $val;
        $val            = null;
        break;
      case 'H':
        $output['hour'] = $val;
        $val            = null;
        break;
      case 'M':
        $output['min']  = $val;
        $val            = null;
        break;
      case 'S':
        $output['sec']  = $val;
        $val            = null;
        break;
      default:
        if( !ctype_digit( $duration{$ix} ))
          return false; // unknown duration controll character  !?!?
        else
          $val .= $duration{$ix};
     }
   }
   return $this->_duration_array( $output );
  }
/**
 * if exist, remove key with expected value from array and return spec. value
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.19 - 2007-03-18
 * @param array $array
 * @param string $expkey
 * @param string $expval
 * @param int $hitval optional
 * @return int
 */
  function _existRem( &$array, $expkey, $expval=FALSE, $hitval=null ) {
    if( !is_array( $array ) || ( 0 == count( $array )))
      return null;
    if( !isset( $array[$expkey] ))
      return null;
    if( !$expval )
      return $hitval;
    if( $expval != $array[$expkey] )
      return null;
    unset( $array[$expkey] );
    return $hitval;
  }
/**
 * creates formatted output for calendar component property data value type date/date-time
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 1.x.x - 2007-05-20
 * @param array   $datetime
 * @param int     $parno, optional, default 6
 * @return string
 */
  function _format_date_time( $datetime, $parno=6 ) {
    if( !isset( $datetime['year'] )  &&
        !isset( $datetime['month'] ) &&
        !isset( $datetime['day'] )   &&
        !isset( $datetime['hour'] )  &&
        !isset( $datetime['min'] )   &&
        !isset( $datetime['sec'] ))
      return ;
    $output = null;
    $output = date('Ymd', mktime ( 0, 0, 0
                                 , (integer) $datetime['month']
                                 , (integer) $datetime['day']
                                 , (integer) $datetime['year']));
    if( isset( $datetime['hour'] )  ||
        isset( $datetime['min'] )   ||
        isset( $datetime['sec'] )   ||
        isset( $datetime['tz'] )) {
      if( isset( $datetime['tz'] )  &&
         !isset( $datetime['hour'] ))
        $datetime['hour'] = '0';
      if( isset( $datetime['hour'] )  &&
         !isset( $datetime['min'] ))
        $datetime['min'] = '0';
      if( isset( $datetime['hour'] )  &&
          isset( $datetime['min'] )   &&
         !isset( $datetime['sec'] ))
        $datetime['sec'] = '0';
      foreach( $datetime as $dkey => $dvalue ) {
        if( 'tz' != $dkey )
          $datetime[$dkey] = (integer) $dvalue;
      }
      $output .= date('\THis', mktime ( $datetime['hour']
                                      , $datetime['min']
                                      , $datetime['sec']
                                      , $datetime['month']
                                      , $datetime['day']
                                      , $datetime['year']));
      if( isset( $datetime['tz'] ) && ( '' < trim ( $datetime['tz'] ))) {
        $datetime['tz'] = trim( $datetime['tz'] );
        $offset = 0;
        if( 'Z' == $datetime['tz'] ) {
          $output .= 'Z';
        }
        if((     5  == strlen( $datetime['tz'] )) &&
           ( '0000' <= substr( $datetime['tz'], -4 )) &&
           ( '9999' >= substr( $datetime['tz'], -4 )) &&
             (( '+' == substr( $datetime['tz'], 0, 1 )) ||
              ( '-' == substr( $datetime['tz'], 0, 1 )))) {
          $hours2sec = substr( $datetime['tz'], 1, 2 ) * 3600;
          $min2sec   = substr( $datetime['tz'], -2 )   * 60;
          $sign      = substr( $datetime['tz'], 0, 1 );
          $offset    = $sign.'1' * ($hours2sec + $min2sec );
        }
        elseif(( 7  == strlen( $datetime['tz'] )) &&
         ( '000000' <= substr( $datetime['tz'], -6 )) &&
         ( '999999' >= substr( $datetime['tz'], -6 )) &&
             (( '+' == substr( $datetime['tz'], 0, 1 )) ||
              ( '-' == substr( $datetime['tz'], 0, 1 )))) {
          $hours2sec = substr( $datetime['tz'], 1, 2 ) * 3600;
          $min2sec   = substr( $datetime['tz'], 3, 2 ) *   60;
          $sec       = substr( $datetime['tz'], -2 );
          $sign      = substr( $datetime['tz'], 0, 1 );
          $offset    = $sign.'1' * ( $hours2sec + $min2sec + $sec );
        }
        if( 0 != $offset ) {
          $output    = date('Ymd\THis\Z', mktime ( $datetime['hour']
                                                 , $datetime['min']
                                                 , $datetime['sec'] + $offset
                                                 , $datetime['month']
                                                 , $datetime['day']
                                                 , $datetime['year']));
        }
      }
      elseif( 7 == $parno )
        $output .= 'Z';
    }
    return $output;
  }
/**
 * creates formatted output for calendar component property data value type duration
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 1.x.x - 2007-05-13
 * @param array $duration ( week, day, hour, min, sec )
 * @return string
 */
  function _format_duration( $duration ) {
    if( !isset( $duration['week'] ) &&
        !isset( $duration['day'] )  &&
        !isset( $duration['hour'] ) &&
        !isset( $duration['min'] )  &&
        !isset( $duration['sec'] ))
      return;
    $output = 'P';
    if( isset( $duration['week'] ) && ( 0 < $duration['week'] ))
      $output       .= $duration['week'].'W';
    else {
      if( isset($duration['day'] ) && ( 0 < $duration['day'] ))
        $output     .= $duration['day'].'D';
      if(( isset( $duration['hour']) && ( 0 < $duration['hour'] )) ||
         ( isset( $duration['min'])  && ( 0 < $duration['min'] ))  ||
         ( isset( $duration['sec'])  && ( 0 < $duration['sec'] ))) {
        $output     .= 'T';
        if( 0 < $duration['hour'] ) {
          $output   .= $duration['hour'].'H';
          if( 0 < $duration['min'] ) {
            $output .= $duration['min'].'M';
            if( 0 < $duration['sec'] )
              $output .= $duration['sec'].'S';
          }
          elseif( 0 < $duration['sec'] )
            $output .= '0M'.$duration['sec'].'S';
        }
        elseif( 0 < $duration['min'] ) {
          $output   .= '0H'.$duration['min'].'M';
          if( 0 < $duration['sec'] )
            $output .= $duration['sec'].'S';
        }
        elseif( 0 < $duration['sec'] )
          $output   .= '0H0M'.$duration['sec'].'S';
      }
    }
    return $output;
  }
/**
 * creates formatted output for calendar component property data value type recur
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.7 - 2006-11-20
 * @param array $recurlabel
 * @param array $recurdata
 * @return string
 */
  function _format_recur ( $recurlabel, $recurdata ) {
    $recur = null;
    foreach( $recurdata as $therule ) {
      $attributes = ( isset( $therule['params'] )) ? $this->_createParams( $therule['params'] ) : null;
      $content1  = $content2  = null;
      foreach( $therule['value'] as $rulelabel => $rulevalue ) {
        switch( $rulelabel ) {
          case 'FREQ': {
            $content1 .= "FREQ=$rulevalue";
            break;
          }
          case 'UNTIL': {
            $content2 .= ";UNTIL=";
            $content2 .= $this->_format_date_time( $rulevalue );
            break;
          }
          case 'COUNT':
          case 'INTERVAL':
          case 'WKST': {
            $content2 .= ";$rulelabel=$rulevalue";
            break;
          }
          case 'BYSECOND':
          case 'BYMINUTE':
          case 'BYHOUR':
          case 'BYMONTHDAY':
          case 'BYYEARDAY':
          case 'BYWEEKNO':
          case 'BYMONTH':
          case 'BYSETPOS': {
            $content2 .= ";$rulelabel=";
            if( is_array( $rulevalue )) {
              foreach( $rulevalue as $vix => $valuePart ) {
                $content2 .= ( $vix ) ? ',' : null;
                $content2 .= $valuePart;
              }
            }
            else
             $content2 .= $rulevalue;
            break;
          }
          case 'BYDAY': {
            $content2 .= ";$rulelabel=";
            $bydaycnt = 0;
            foreach( $rulevalue as $vix => $valuePart ) {
              $content21 = $content22 = null;
              if( is_array( $valuePart )) {
                $content2 .= ( $bydaycnt ) ? ',' : null;
                foreach( $valuePart as $vix2 => $valuePart2 ) {
                  if( 'DAY' != strtoupper( $vix2 ))
                      $content21 .= $valuePart2;
                  else
                    $content22 .= $valuePart2;
                }
                $content2 .= $content21.$content22;
                $bydaycnt++;
              }
              else {
                $content2 .= ( $bydaycnt ) ? ',' : null;
                if( 'DAY' != strtoupper( $vix ))
                    $content21 .= $valuePart;
                else {
                  $content22 .= $valuePart;
                  $bydaycnt++;
                }
                $content2 .= $content21.$content22;
              }
            }
            break;
          }
          default: {
            $content2 .= ";$rulelabel=$rulevalue";
            break;
          }
        }
      }
      $recur .= $this->_createElement( $recurlabel, $attributes, $content1.$content2 );
    }
    return $recur;
  }
/**
 * create property name case - lower/upper
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.7 - 2006-11-20
 * @param string $propertyName
 * @return string
 */
  function _formatPropertyName( $propertyName ) {
    switch( $this->format ) {
      case 'xcal':
        return strtolower( $propertyName );
        break;
      default:
        return strtoupper( $propertyName );
        break;
    }
  }
/**
 * set input (formatted) parameters- component property attributes
 *
 * default parameters can be set, if missing
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 1.x.x - 2007-05-01
 * @param array $params
 * @param array $defaults
 * @return array
 */
  function _setParams( $params, $defaults=FALSE ) {
    if( !is_array( $params))
      $params = array();
    $input = array();
    foreach( $params as $paramKey => $paramValue ) {
      if( is_array( $paramValue )) {
        foreach( $paramValue as $pkey => $pValue ) {
          if(( '"' == substr( $pValue, 0, 1 )) && ( '"' == substr( $pValue, -1 )))
            $paramValue[$pkey] = substr( $pValue, 1, ( strlen( $pValue ) - 2 ));
        }
      }
      elseif(( '"' == substr( $paramValue, 0, 1 )) && ( '"' == substr( $paramValue, -1 )))
        $paramValue = substr( $paramValue, 1, ( strlen( $paramValue ) - 2 ));
      if( 'VALUE' == strtoupper( $paramKey ))
        $input['VALUE']                 = strtoupper( $paramValue );
      else
        $input[strtoupper( $paramKey )] = $paramValue;
    }
    if( is_array( $defaults )) {
      foreach( $defaults as $paramKey => $paramValue ) {
        if( !isset( $input[$paramKey] ))
          $input[$paramKey] = $paramValue;
      }
    }
    return (0 < count( $input )) ? $input : null;
  }
/*********************************************************************************/
/*********************************************************************************/
/**
 * get general component config variables or info about subcomponents
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 1.x.x - 2007-05-24
 * @param string $config
 * @return value
 */
  function getConfig( $config ) {
    switch( strtoupper( $config )) {
      case 'COMPSINFO':
        unset( $this->compix );
        $info = array();
        foreach( $this->subcomponents as $cix => $component ) {
          unset( $component->propix );
          $info[$cix]['ordno'] = $cix + 1;
          $info[$cix]['type']  = $component->objName;
          $info[$cix]['uid']   = $component->getProperty( 'uid' );
          $info[$cix]['props'] = $component->getConfig( 'propinfo' );
          $info[$cix]['sub']   = $component->getConfig( 'compsinfo' );
        }
        return $info;
        break;
      case 'FORMAT':
        return $this->format;
        break;
      case 'LANGUAGE':
         // get language for calendar component as defined in [RFC 1766]
        return $this->language;
        break;
      case 'NL':
        return $this->nl;
        break;
      case 'PROPINFO':
        $output = array();
        if( 0 < count( $this->action ))
          $output['ACTION'] = count( $this->action ) / 2;
        if( 0 < count( $this->attach ))
          $output['ATTACH'] = count( $this->attach );
        if( 0 < count( $this->attendee ))
          $output['ATTENDEE'] = count( $this->attendee );
        if( 0 < count( $this->categories ))
          $output['CATEGORIES'] = count( $this->categories );
        if( 0 < count( $this->class ))
          $output['CLASS'] = count( $this->class ) / 2;
        if( 0 < count( $this->comment ))
          $output['COMMENT'] = count( $this->comment );
        if( 0 < count( $this->completed ))
          $output['COMPLETED'] = count( $this->completed ) / 2;
        if( 0 < count( $this->contact ))
          $output['CONTACT'] = count( $this->contact );
        if( 0 < count( $this->created ))
          $output['CREATED'] = count( $this->created ) / 2;
        if( 0 < count( $this->description ))
          $output['DESCRIPTION'] = count( $this->description );
        if( 0 < count( $this->dtend ))
          $output['DTEND'] = count( $this->dtend ) / 2;
        if( 0 < count( $this->dtstart ))
          $output['DTSTART'] = count( $this->dtstart ) / 2;
        if( 0 < count( $this->dtstamp ))
          $output['DTSTAMP'] = count( $this->dtstamp ) / 2;
        if( 0 < count( $this->due ))
          $output['DUE'] = count( $this->due ) / 2;
        if( 0 < count( $this->duration ))
          $output['DURATION'] = count( $this->duration ) / 2;
        if( 0 < count( $this->exdate ))
          $output['EXDATE'] = count( $this->exdate );
        if( 0 < count( $this->exrule ))
          $output['EXRULE'] = count( $this->exrule );
        if( 0 < count( $this->freebusy ))
          $output['FREEBUSY'] = count( $this->freebusy );
        if( 0 < count( $this->geo ))
          $output['GEO'] = count( $this->geo ) / 2;
        if( 0 < count( $this->lastmodified ))
          $output['LAST-MODIFIED'] = count( $this->lastmodified ) / 2;
        if( 0 < count( $this->location ))
          $output['LOCATION'] = count( $this->location ) / 2;
        if( 0 < count( $this->organizer ))
          $output['ORGANIZER'] = count( $this->organizer ) / 2;
        if( 0 < count( $this->percentcomplete ))
          $output['PERCENT-COMPLETE'] = count( $this->percentcomplete ) / 2;
        if( 0 < count( $this->priority ))
          $output['PRIORITY'] = count( $this->priority ) / 2;
        if( 0 < count( $this->rdate ))
          $output['RDATE'] = count( $this->rdate );
        if( 0 < count( $this->recurrenceid ))
          $output['RECURRENCE-ID'] = count( $this->recurrenceid ) / 2;
        if( 0 < count( $this->relatedto ))
          $output['RELATED-TO'] = count( $this->relatedto );
        if( 0 < count( $this->repeat ))
          $output['REPEAT'] = count( $this->repeat ) / 2;
        if( 0 < count( $this->requeststatus ))
          $output['REQUEST-STATUS'] = count( $this->requeststatus );
        if( 0 < count( $this->resources ))
          $output['RESOURCES'] = count( $this->resources );
        if( 0 < count( $this->sequence ))
          $output['SEQUENCE'] = count( $this->sequence ) / 2;
        if( 0 < count( $this->rrule ))
          $output['RRULE'] = count( $this->rrule );
        if( 0 < count( $this->status ))
          $output['STATUS'] = count( $this->status ) / 2;
        if( 0 < count( $this->summary ))
          $output['SUMMARY'] = count( $this->summary ) / 2;
        if( 0 < count( $this->transp ))
          $output['TRANSP'] = count( $this->transp ) / 2;
        if( 0 < count( $this->trigger ))
          $output['TRIGGER'] = count( $this->trigger ) / 2;
        if( 0 < count( $this->tzid ))
          $output['TZID'] = count( $this->tzid ) / 2;
        if( 0 < count( $this->tzname ))
          $output['TZNAME'] = count( $this->tzname );
        if( 0 < count( $this->tzoffsetfrom ))
          $output['TZOFFSETTFROM'] = count( $this->tzoffsetfrom ) / 2;
        if( 0 < count( $this->tzoffsetto ))
          $output['TZOFFSETTO'] = count( $this->tzoffsetto ) / 2;
        if( 0 < count( $this->tzurl ))
          $output['TZURL'] = count( $this->tzurl ) / 2;
        if( !in_array( $this->objName, array( 'valarm', 'vtimezone' ))) {
          if( empty( $this->uid['value'] ))
            $this->_makeuid();
          $output['UID'] = 1;
        }
        if( 0 < count( $this->url ))
          $output['URL'] = count( $this->url ) / 2;
        if( 0 < count( $this->xprop ))
          $output['X-PROP'] = count( $this->xprop );
        return $output;
        break;
      case 'UNIQUE_ID':
        if( empty( $this->unique_id ))
          $this->unique_id  = gethostbyname( $_SERVER['SERVER_NAME'] );
        return $this->unique_id;
        break;
    }
  }
/**
 * general component config setting
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.22 - 2007-04-06
 * @param string $config
 * @param string $value
 * @return void
 */
  function setConfig( $config, $value ) {
    switch( strtoupper( $config )) {
      case 'FORMAT':
        $this->format = $value;
        break;
      case 'LANGUAGE':
         // set language for calendar component as defined in [RFC 1766]
        $this->language = $value;
        break;
      case 'NL':
        $this->nl = $value;
        break;
      case 'UNIQUE_ID':
        $this->unique_id = $value;
        break;
    }
  }
/*********************************************************************************/
/**
 * get component property value/params
 *
 * if property has multiply values, consequtive function calls are needed
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 1.x.x - 2007-05-24
 * @param string $propName, optional
 * @param int @propix, optional, if specific property is wanted in case of multiply occurences
 * @param bool $inclParam=FALSE
 * @return mixed
 */
  function getProperty( $propName=FALSE, $propix=FALSE, $inclParam=FALSE ) {
    $propName = ( $propName ) ? strtoupper( $propName ) : 'X-PROP';
    if( !$propix )
      $propix = ( isset( $this->propix[$propName] )) ? $this->propix[$propName] + 2 : 1;
    $this->propix[$propName] = --$propix;
    switch( $propName ) {
      case 'ACTION':
        if(( 0 < $propix ) || !isset( $this->action['value'] )) return FALSE;
        return ( $inclParam ) ? $this->action : $this->action['value'];
        break;
      case 'ATTACH':
        if( count( $this->attach ) <= $propix ) return FALSE;
        return ( $inclParam ) ? $this->attach[$propix] : $this->attach[$propix]['value'];
        break;
      case 'ATTENDEE':
        if( count( $this->attendee ) <= $propix ) return FALSE;
        return ( $inclParam ) ? $this->attendee[$propix] : $this->attendee[$propix]['value'];
        break;
      case 'CATEGORIES':
        if( count( $this->categories ) <= $propix ) return FALSE;
        return ( $inclParam ) ? $this->categories[$propix] : $this->categories[$propix]['value'];
        break;
      case 'CLASS':
        if(( 0 < $propix ) || !isset( $this->class['value'] )) return FALSE;
        return ( $inclParam ) ? $this->class : $this->class['value'];
        break;
      case 'COMMENT':
        if( count( $this->comment ) <= $propix ) return FALSE;
        return ( $inclParam ) ? $this->comment[$propix] : $this->comment[$propix]['value'];
        break;
      case 'COMPLETED':
        if(( 0 < $propix ) || !isset( $this->completed['value'] )) return FALSE;
        return ( $inclParam ) ? $this->completed : $this->completed['value'];
        break;
      case 'CONTACT':
        if( count( $this->contact ) <= $propix ) return FALSE;
        return ( $inclParam ) ? $this->contact[$propix] : $this->contact[$propix]['value'];
        break;
      case 'CREATED':
        if(( 0 < $propix ) || !isset( $this->created['value'] )) return FALSE;
        return ( $inclParam ) ? $this->created : $this->created['value'];
        break;
      case 'DESCRIPTION':
        if( count( $this->description ) <= $propix ) return FALSE;
        return ( $inclParam ) ? $this->description[$propix] : $this->description[$propix]['value'];
        break;
      case 'DTEND':
        if(( 0 < $propix ) || !isset( $this->dtend['value'] )) return FALSE;
        return ( $inclParam ) ? $this->dtend : $this->dtend['value'];
        break;
      case 'DTSTAMP':
        if( in_array( $this->objName, array( 'valarm', 'vtimezone' )))
          return;
        if( 0 < $propix )
          return null;
        if( !isset( $this->dtstamp['value'] ))
          $this->_makeDtstamp();
        return ( $inclParam ) ? $this->dtstamp : $this->dtstamp['value'];
        break;
      case 'DTSTART':
        if(( 0 < $propix ) || !isset( $this->dtstart['value'] )) return FALSE;
        return ( $inclParam ) ? $this->dtstart : $this->dtstart['value'];
        break;
      case 'DUE':
        if(( 0 < $propix ) || !isset( $this->due['value'] )) return FALSE;
        return ( $inclParam ) ? $this->due : $this->due['value'];
        break;
      case 'DURATION':
        if(( 0 < $propix ) || !isset( $this->duration['value'] )) return FALSE;
        return ( $inclParam ) ? $this->duration : $this->duration['value'];
        break;
      case 'EXDATE':
        if( count( $this->exdate ) <= $propix ) return FALSE;
        return ( $inclParam ) ? $this->exdate[$propix] : $this->exdate[$propix]['value'];
        break;
      case 'EXRULE':
        if( count( $this->exrule ) <= $propix ) return FALSE;
        return ( $inclParam ) ? $this->exrule[$propix] : $this->exrule[$propix]['value'];
        break;
      case 'FREEBUSY':
        if( count( $this->freebusy ) <= $propix ) return FALSE;
        return ( $inclParam ) ? $this->freebusy[$propix] : $this->freebusy[$propix]['value'];
        break;
      case 'GEO':
        if(( 0 < $propix ) || !isset( $this->geo['value'] )) return FALSE;
        return ( $inclParam ) ? $this->geo : $this->geo['value'];
        break;
      case 'LAST-MODIFIED':
        if(( 0 < $propix ) || !isset( $this->lastmodified['value'] )) return FALSE;
        return ( $inclParam ) ? $this->lastmodified : $this->lastmodified['value'];
        break;
      case 'LOCATION':
        if(( 0 < $propix ) || !isset( $this->location['value'] )) return FALSE;
        return ( $inclParam ) ? $this->location : $this->location['value'];
        break;
      case 'ORGANIZER':
        if(( 0 < $propix ) || !isset( $this->organizer['value'] )) return FALSE;
        return ( $inclParam ) ? $this->organizer : $this->organizer['value'];
        break;
      case 'PERCENT-COMPLETE':
        if(( 0 < $propix ) || !isset( $this->percentcomplete['value'] )) return FALSE;
        return ( $inclParam ) ? $this->percentcomplete : $this->percentcomplete['value'];
        break;
      case 'PRIORITY':
        if(( 0 < $propix ) || !isset( $this->priority['value'] )) return FALSE;
        return ( $inclParam ) ? $this->priority : $this->priority['value'];
        break;
      case 'RDATE':
        if( count( $this->rdate ) <= $propix ) return FALSE;
        return ( $inclParam ) ? $this->rdate[$propix] : $this->rdate[$propix]['value'];
        break;
      case 'RECURRENCE-ID':
        if(( 0 < $propix ) || !isset( $this->recurrenceid['value'] )) return FALSE;
        return ( $inclParam ) ? $this->recurrenceid : $this->recurrenceid['value'];
        break;
      case 'RELATED-TO':
        if( count( $this->relatedto ) <= $propix ) return FALSE;
        return ( $inclParam ) ? $this->relatedto[$propix] : $this->relatedto[$propix]['value'];
        break;
      case 'REPEAT':
        if(( 0 < $propix ) || !isset( $this->repeat['value'] )) return FALSE;
        return ( $inclParam ) ? $this->repeat : $this->repeat['value'];
        break;
      case 'REQUEST-STATUS':
        if( count( $this->requeststatus ) <= $propix ) return FALSE;
        return ( $inclParam ) ? $this->requeststatus[$propix] : $this->requeststatus[$propix]['value'];
        break;
      case 'RESOURCES':
        if( count( $this->resources ) <= $propix ) return FALSE;
        return ( $inclParam ) ? $this->resources[$propix] : $this->resources[$propix]['value'];
        break;
      case 'RRULE':
        if( count( $this->rrule ) <= $propix ) return FALSE;
        return ( $inclParam ) ? $this->rrule[$propix] : $this->rrule[$propix]['value'];
        break;
      case 'SEQUENCE':
        if(( 0 < $propix ) || !isset( $this->sequence['value'] )) return FALSE;
        return ( $inclParam ) ? $this->sequence : $this->sequence['value'];
        break;
      case 'STATUS':
        if(( 0 < $propix ) || !isset( $this->status['value'] )) return FALSE;
        return ( $inclParam ) ? $this->status : $this->status['value'];
        break;
      case 'SUMMARY':
        if(( 0 < $propix ) || !isset( $this->summary['value'] )) return FALSE;
        return ( $inclParam ) ? $this->summary : $this->summary['value'];
        break;
      case 'TRANSP':
        if(( 0 < $propix ) || !isset( $this->transp['value'] )) return FALSE;
        return ( $inclParam ) ? $this->transp : $this->transp['value'];
        break;
      case 'TRIGGER':
        if(( 0 < $propix ) || !isset( $this->trigger['value'] )) return FALSE;
        return ( $inclParam ) ? $this->trigger : $this->trigger['value'];
        break;
      case 'TZID':
        if(( 0 < $propix ) || !isset( $this->tzid['value'] )) return FALSE;
        return ( $inclParam ) ? $this->tzid : $this->tzid['value'];
        break;
      case 'TZNAME':
        if( count( $this->tzname ) <= $propix ) return FALSE;
        return ( $inclParam ) ? $this->tzname[$propix] : $this->tzname[$propix]['value'];
        break;
      case 'TZOFFSETFROM':
        if(( 0 < $propix ) || !isset( $this->tzoffsetfrom['value'] )) return FALSE;
        return ( $inclParam ) ? $this->tzoffsetfrom : $this->tzoffsetfrom['value'];
        break;
      case 'TZOFFSETTO':
        if(( 0 < $propix ) || !isset( $this->tzoffsetto['value'] )) return FALSE;
        return ( $inclParam ) ? $this->tzoffsetto : $this->tzoffsetto['value'];
        break;
      case 'TZURL':
        if(( 0 < $propix ) || !isset( $this->tzurl['value'] )) return FALSE;
        return ( $inclParam ) ? $this->tzurl : $this->tzurl['value'];
        break;
      case 'UID':
        if( in_array( $this->objName, array( 'valarm', 'vtimezone' )))
          return;
        if( 0 < $propix )
          return null;
        if( empty( $this->uid['value'] ))
          $this->_makeuid();
        return ( $inclParam ) ? $this->uid : $this->uid['value'];
        break;
      case 'URL':
        if(( 0 < $propix ) || !isset( $this->url['value'] )) return FALSE;
        return ( $inclParam ) ? $this->url : $this->url['value'];
        break;
      default:
        if( count( $this->xprop ) <= $propix ) return FALSE;
        return ( $inclParam ) ? $this->xprop[$propix] : $this->xprop[$propix]['value'];
        break;
    }
    return FALSE;
  }
/**
 * general component property setting
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 1.x.x - 2007-04-25
 * @param mixed $args variable number of function arguments,
 *                    first argument is ALWAYS component name!
 * @return void
 */
  function setProperty() {
    $numargs    = func_num_args();
    if( 1 > $numargs )
      return FALSE;
    $arglist    = func_get_args();
    $arglist[0] = strtoupper( $arglist[0] );
    for( $argix=$numargs; $argix < 12; $argix++ )
      if( !isset( $arglist[$argix] ))
        $arglist[$argix] = null;
    switch( $arglist[0] ) {
      case 'ACTION':
        $this->setAction( $arglist[1], $arglist[2] );
        break;
      case 'ATTACH':
        $this->setAttach( $arglist[1], $arglist[2] );
        break;
      case 'ATTENDEE':
        $this->setAttendee( $arglist[1], $arglist[2] );
        break;
      case 'CATEGORIES':
        $this->setCategories( $arglist[1], $arglist[2] );
        break;
      case 'CLASS':
        $this->setClass( $arglist[1], $arglist[2] );
        break;
      case 'COMMENT':
        $this->setComment( $arglist[1], $arglist[2] );
        break;
      case 'COMPLETED':
        $this->setCompleted( $arglist[1], $arglist[2], $arglist[3], $arglist[4], $arglist[5], $arglist[6], $arglist[7] );
        break;
      case 'CONTACT':
        $this->setContact( $arglist[1], $arglist[2] );
        break;
      case 'CREATED':
        $this->setCreated( $arglist[1], $arglist[2], $arglist[3], $arglist[4], $arglist[5], $arglist[6], $arglist[7] );
        break;
      case 'DESCRIPTION':
        $this->setDescription( $arglist[1], $arglist[2] );
        break;
      case 'DTEND':
        $this->setDtend( $arglist[1], $arglist[2], $arglist[3], $arglist[4], $arglist[5], $arglist[6], $arglist[7], $arglist[8] );
        break;
      case 'DTSTAMP':
        $this->setDtstamp( $arglist[1], $arglist[2], $arglist[3], $arglist[4], $arglist[5], $arglist[6], $arglist[7] );
        break;
      case 'DTSTART':
        $this->setDtstart( $arglist[1], $arglist[2], $arglist[3], $arglist[4], $arglist[5], $arglist[6], $arglist[7], $arglist[8] );
        break;
      case 'DUE':
        $this->setDue( $arglist[1], $arglist[2], $arglist[3], $arglist[4], $arglist[5], $arglist[6], $arglist[7], $arglist[8] );
        break;
      case 'DURATION':
        $this->setDuration( $arglist[1], $arglist[2], $arglist[3], $arglist[4], $arglist[5], $arglist[6] );
        break;
      case 'EXDATE':
        $this->setExdate( $arglist[1], $arglist[2] );
        break;
      case 'EXRULE':
        $this->setExrule( $arglist[1], $arglist[2] );
        break;
      case 'FREEBUSY':
        $this->setFreebusy( $arglist[1], $arglist[2], $arglist[3] );
        break;
      case 'GEO':
        $this->setGeo( $arglist[1], $arglist[2], $arglist[3] );
        break;
      case 'LAST-MODIFIED':
        $this->setLastModified( $arglist[1], $arglist[2], $arglist[3], $arglist[4], $arglist[5], $arglist[6], $arglist[7] );
        break;
      case 'LOCATION':
        $this->setLocation( $arglist[1], $arglist[2] );
        break;
      case 'ORGANIZER':
        $this->setOrganizer( $arglist[1], $arglist[2] );
        break;
      case 'PERCENT-COMPLETE':
        $this->setPercentComplete( $arglist[1], $arglist[2] );
        break;
      case 'PRIORITY':
        $this->setPriority( $arglist[1], $arglist[2] );
        break;
      case 'RDATE':
        $this->setRdate( $arglist[1], $arglist[2] );
        break;
      case 'RECURRENCE-ID':
        $this->setRecurrenceid( $arglist[1], $arglist[2], $arglist[3], $arglist[4], $arglist[5], $arglist[6], $arglist[7], $arglist[8] );
       break;
      case 'RELATED-TO':
        $this->setRelatedTo( $arglist[1], $arglist[2] );
        break;
      case 'REPEAT':
        $this->setRepeat( $arglist[1], $arglist[2] );
        break;
      case 'REQUEST-STATUS':
        $this->setRequestStatus( $arglist[1], $arglist[2], $arglist[3], $arglist[4] );
        break;
      case 'RESOURCES':
        $this->setResources( $arglist[1], $arglist[2] );
        break;
      case 'RRULE':
        $this->setRrule( $arglist[1], $arglist[2] );
        break;
      case 'SEQUENCE':
        $this->setSequence( $arglist[1], $arglist[2] );
        break;
      case 'STATUS':
        $this->setStatus( $arglist[1], $arglist[2] );
        break;
      case 'SUMMARY':
        $this->setSummary( $arglist[1], $arglist[2] );
        break;
      case 'TRANSP':
        $this->setTransp( $arglist[1], $arglist[2] );
        break;$bodytag = str_replace("%body%", "black", "<body text=%body%>");
      case 'TRIGGER':
        $this->setTrigger( $arglist[1], $arglist[2], $arglist[3], $arglist[4], $arglist[5], $arglist[6], $arglist[7], $arglist[8], $arglist[9], $arglist[10], $arglist[11] );
        break;
      case 'TZID':
        $this->setTzid( $arglist[1], $arglist[2] );
        break;
      case 'TZNAME':
        $this->setTzname( $arglist[1], $arglist[2] );
        break;
      case 'TZOFFSETFROM':
        $this->setTzoffsetfrom( $arglist[1], $arglist[2] );
        break;
      case 'TZOFFSETTO':
        $this->setTzoffsetto( $arglist[1], $arglist[2] );
        break;
      case 'TZURL':
        $this->setTzurl( $arglist[1], $arglist[2] );
        break;
      case 'UID':
        $this->setUid( $arglist[1], $arglist[2] );
        break;
      case 'URL':
        $this->setUrl( $arglist[1], $arglist[2] );
        break;
      default:
        $this->setXprop( $arglist[0], $arglist[1], $arglist[2] );
        break;
    }
  }
/*********************************************************************************/
/**
 * parse component unparsed data into properties
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 1.x.x - 2007-04-27
 * @return bool FALSE if error occurs during parsing
 *
 */
  function parse() {
    if( !is_array( $this->unparsed ) || ( 0 >= count( $this->unparsed )))
      return FALSE;
            /* concatenate property values spread over several lines */
    $lastix    = -1;
    $propnames = array( 'action', 'attach', 'attendee', 'categories', 'comment', 'completed'
                      , 'contact', 'class', 'created', 'description', 'dtend', 'dtstart'
                      , 'dtstamp', 'due', 'duration', 'exdate', 'exrule', 'freebusy', 'geo'
                      , 'last-modified', 'location', 'organizer', 'percent-complete'
                      , 'priority', 'rdate', 'recurrence-id', 'related-to', 'repeat'
                      , 'request-status', 'resources', 'rrule', 'sequence', 'status'
                      , 'summary', 'transp', 'trigger', 'tzid', 'tzname', 'tzoffsetfrom'
                      , 'tzoffsetto', 'tzurl', 'uid', 'url', 'x-' );
    $unparsed  = array();
    foreach( $this->unparsed as $line ) {
      $newProp = FALSE;
      foreach ( $propnames as $propname ) {
        if( $propname == strtolower( substr( $line, 0, strlen( $propname )))) {
          $newProp = TRUE;
          break;
        }
      }
      if( $newProp ) {
        $newProp = FALSE;
        $lastix++;
        $unparsed[$lastix]  = $line;
      }
      else {
            /* remove line breaks */
        if(( '\n' == substr( $unparsed[$lastix], - 2 )) &&
           (  ' ' == substr( $line, 0, 1 ))) {
          $unparsed[$lastix] = substr( $unparsed[$lastix], 0, strlen( $unparsed[$lastix] ) - 2 );
          $line = substr( $line, 1 );
        }
        $unparsed[$lastix] .= $line;
      }
    }
            /* parse each property 'line' */
    foreach( $unparsed as $line ) {
      if( '\n' == substr( $line, - 2 ))
        $line = substr( $line, 0, strlen( $line ) - 2 );
            /* get propname, (problem with x-properties, otherwise in previous loop) */
      $cix = $propname = null;
      for( $cix=0; $cix < strlen( $line ); $cix++ ) {
        if( in_array( $line{$cix}, array( ':', ';' )))
          break;
        else
          $propname .= $line{$cix};
      }
            /* rest of the line is opt.params and value */
      $line = substr( $line, $cix);
            /* separate attributes from value */
      $attr   = array();
      $attrix = -1;
      for( $cix=0; $cix < strlen( $line ); $cix++ ) {
        if((       ':'   == $line{$cix} ) &&
           (       '://' != substr( $line, $cix, 3 )) &&
           ( 'mailto:'   != strtolower( substr( $line, $cix - 6, 7 )))) {
          $attrEnd = TRUE;
          if( ctype_digit( $line{$cix+1} )) { // an URI with a portnr??
            for( $c2ix = $cix; 3 < $c2ix; $c2ix-- ) {
              if( '://' == substr( $line, $c2ix - 2, 3 )) {
                $attrEnd = FALSE;
                break; // an URI with a portnr!!
              }
            }
          }
          if( $attrEnd) {
            $line = substr( $line, $cix + 1 );
            break;
          }
        }
        if( ';' == $line{$cix} )
          $attr[++$attrix] = null;
        else
          $attr[$attrix] .= $line{$cix};
      }
            /* make attributes in array format */
      $propattr = array();
      foreach( $attr as $attribute ) {
        $attrsplit = explode( '=', $attribute, 2 );
        if( 1 < count( $attrsplit ))
          $propattr[$attrsplit[0]] = $attrsplit[1];
        else
          $propattr[] = $attribute;
      }
            /* call setProperty( $propname.. . */
      switch( $propname ) {
        case 'ATTENDEE':
          foreach( $propattr as $pix => $attr ) {
            $attr2 = explode( ',', $attr );
              if( 1 < count( $attr2 ))
                $propattr[$pix] = $attr2;
          }
          $this->setProperty( $propname, $line, $propattr );
          break;
        case 'COMMENT':
        case 'CONTACT':
        case 'DESCRIPTION':
        case 'LOCATION':
        case 'SUMMARY':
          $this->setProperty( $propname, $this->_strunrep( $line ), $propattr );
          break;
        case 'REQUEST-STATUS':
          $values = explode( ';', $line );
          if( !isset( $values[2] ))
            $values[2] = FALSE;
          $this->setProperty( $propname
                            , $values[0]                     // statcode
                            , $this->_strunrep( $values[1] ) // statdesc
                            , $this->_strunrep( $values[2] ) // extdata
                            , $propattr );
          break;
        case 'FREEBUSY':
          $fbtype = $propattr['FBTYPE'];
          unset( $propattr['FBTYPE'] );
          $values = explode( ',', $line );
          foreach( $values as $vix => $value ) {
            $value2 = explode( '/', $value );
            if( 1 < count( $value2 ))
              $values[$vix] = $value2;
          }
          $this->setProperty( $propname, $fbtype, $values, $propattr );
          break;
        case 'GEO':
          $value= explode( ';', $line, 2 );
          $this->setProperty( $propname, $value[0], $value[1], $propattr );
          break;
        case 'EXDATE':
          $values= explode( ',', $line );
          $this->setProperty( $propname, $values, $propattr );
          break;
        case 'RDATE':
          $values = explode( ',', $line );
          foreach( $values as $vix => $value ) {
            $value2 = explode( '/', $value );
            if( 1 < count( $value2 ))
              $values[$vix] = $value2;
          }
          $this->setProperty( $propname, $values, $propattr );
          break;
        case 'EXRULE':
        case 'RRULE':
          $values = explode( ';', $line );
          $recur = array();
          foreach( $values as $value2 ) {
            $value3 = explode( '=', $value2, 2 );
            $rulelabel = strtoupper( $value3[0] );
            switch( $rulelabel ) {
              case 'BYDAY': {
                $value4 = explode( ',', $value3[1] );
                if( 1 < count( $value4 )) {
                  foreach( $value4 as $v5ix => $value5 ) {
                    $value6 = array();
                    $dayno = $dayname = null;
                    if(( ctype_alpha( substr( $value5, -1 ))) &&
                       ( ctype_alpha( substr( $value5, -2, 1 )))) {
                      $dayname = substr( $value5, -2, 2 );
                      if( 2 < strlen( $value5 ))
                        $dayno = substr( $value5, 0, strlen( $value5 ) - 2 );
                    }
                    if( $dayno )
                      $value6[] = $dayno;
                    if( $dayname )
                      $value6['DAY'] = $dayname;
                    $value4[$v5ix] = $value6;
                  }
                }
                else {
                  $value4 = array();
                  $dayno = $dayname = null;
                  if(( ctype_alpha( substr( $value3[1], -1 ))) &&
                     ( ctype_alpha( substr( $value3[1], -2, 1 )))) {
                    $dayname = array( 'DAY' => substr( $value3[1], -2, 2 ));
                    if( 2 < strlen( $value3[1] ))
                      $dayno = substr( $value3[1], 0, strlen( $value3[1] ) - 2);
                  }
                  if( $dayno )
                    $value4[] = $dayno;
                  if( $dayname )
                    $value4[] = $dayname;
                }
                $recur[$rulelabel] = $value4;
                break;
              }
              default: {
                $value4 = explode( ',', $value3[1] );
                if( 1 < count( $value4 ))
                  $value3[1] = $value4;
                $recur[$rulelabel] = $value3[1];
                break;
              }
            } // end - switch $rulelabel
          } // end - foreach( $values.. .
          $this->setProperty( $propname, $recur, $propattr );
          break;
        default:
          $this->setProperty( $propname, $line, $propattr );
          break;
      } // end  switch( $propname.. .
    } // end - foreach( $this->unparsed.. .
    if( is_array( $this->subcomponents ) && ( 0 < count( $this->subcomponents ))) {
      for( $six = 0; $six < count( $this->subcomponents ); $six++ )
        $this->subcomponents[$six]->parse();
    }
  }
/*********************************************************************************/
/*********************************************************************************/
/**
 * delete calendar subcomponent from component container
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 1.x.x - 2007-05-06
 * @param mixed $arg1 ordno / component type / component uid
 * @param mixed $arg2 optional, ordno if arg1 = component type
 * @return void
 */
  function deleteComponent( $arg1, $arg2=FALSE  ) {
    $argType = $index = null;
    if ( is_int( $arg1 )) {
      $argType = 'INDEX';
      $index = --$arg1;
    }
    elseif(( strlen( $arg1 ) <= strlen( 'vfreebusy' )) && ( FALSE === strpos( $arg1, '@' ))) {
      $argType = strtolower( $arg1 );
      $index = ( is_int( $arg2 )) ? --$arg2 : 0;
    }
    $cix2dC = 0;
    foreach ( $this->subcomponents as $cix => $component) {
      unset( $component->propix );
      if(( 'INDEX' == $argType ) && ( $index == $cix )) {
        unset( $this->subcomponents[$cix] );
        return TRUE;
      }
      elseif( $argType == $component->objName ) {
        if( $index == $cix2dC ) {
          unset( $this->subcomponents[$cix] );
          return TRUE;
        }
        $cix2dC++;
      }
      elseif( !$argType && ($arg1 == $component->getProperty( 'uid' ))) {
        unset( $this->subcomponents[$cix] );
        return TRUE;
      }
    }
    return FALSE;
  }
/**
 * get calendar component subcomponent from component container
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 1.x.x - 2007-05-06
 * @param mixed $arg1 optional, ordno/component type/ component uid
 * @param mixed $arg2 optional, ordno if arg1 = component type
 * @return object
 */
  function getComponent ( $arg1=FALSE, $arg2=FALSE ) {
    $index = $argType = null;
    if ( !$arg1 ) {
      $argType = 'INDEX';
      $index = $this->compix['INDEX'] =
      ( isset( $this->compix[$argType] )) ? $this->compix[$argType] + 1 : 1;
      $index = $index - 1;
    }
    elseif ( is_int( $arg1 )) {
      $argType = 'INDEX';
      $index = $arg1 - 1;
      unset( $this->compix );
    }
    elseif(( strlen( $arg1 ) <= strlen( 'vfreebusy' )) && ( FALSE === strpos( $arg1, '@' ))) {
      unset( $this->compix['INDEX'] );
      $argType = strtolower( $arg1 );
      if( !$arg2 )
        $index = $this->compix[$argType] =
        ( isset( $this->compix[$argType] )) ? $this->compix[$argType] + 1 : 1;
      else
        $index = $arg2;
      $index = $index - 1;
    }
    if( isset( $index) && ( $index > end( array_keys( $this->subcomponents ))))
      return FALSE;
    $cix2gC = 0;
    foreach ( $this->subcomponents as $cix => $component ) {
      unset( $component->propix );
      if(( 'INDEX' == $argType ) && ( $index == $cix ))
        return $component;
      elseif( $argType == $component->objName ) {
         if( $index == $cix2gC )
           return $component;
         $cix2gC++;
      }
      elseif( !$argType && ( $arg1 == $component->getProperty( 'uid' ))) {
        unset( $component->propix );
        return $component;
      }
    }
            /* not found.. . */
    unset( $this->compix );
    return false;
  }
/**
 * add calendar component as subcomponent to container for subcomponents
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 1.x.x - 2007-04-24
 * @param object $component calendar component
 * @return void
 */
  function addSubComponent ( $component ) {
    $this->setComponent( $component );
  }
/**
 * add calendar component as subcomponent to container for subcomponents
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 1.x.x - 2007-04-24
 * @param object $component calendar component
 * @param mixed $arg1 optional, ordno/component type/ component uid
 * @param mixed $arg2 optional, ordno if arg1 = component type
 * @return void
 */
  function setComponent ( $component, $arg1=FALSE, $arg2=FALSE  ) {
    if( '' >= $component->getConfig( 'language'))
      $component->setConfig( 'language' ,  $this->getConfig( 'language' ));
    $component->setConfig( 'nl' ,          $this->getConfig( 'nl' ));
    $component->setConfig( 'unique_id' ,   $this->getConfig( 'unique_id' ));
    $component->setConfig( 'format' ,      $this->getConfig( 'format' ));
    if( !in_array( $component->objName, array( 'valarm', 'vtimezone' ))) {
      unset( $component->propix );
            /* make sure dtstamp and uid is set */
      $dummy = $component->getProperty( 'dtstamp' );
      $dummy = $component->getProperty( 'uid' );
    }
    if( !$arg1 ) {
      $this->subcomponents[] = $component;
      return TRUE;
    }
    $argType = $index = null;
    if ( is_int( $arg1 )) {
      $argType = 'INDEX';
      $index = --$arg1;
    }
    elseif(( strlen( $arg1 ) <= strlen( 'vfreebusy' )) && ( FALSE === strpos( $arg1, '@' ))) {
      $argType = strtolower( $arg1 );
      $index = ( is_int( $arg2 )) ? --$arg2 : 0;
    }
    $cix2sC = 0;
    foreach ( $this->subcomponents as $cix => $subcomponent ) {
      unset( $subcomponent->propix );
      if(( 'INDEX' == $argType ) && ( $index == $cix )) {
        $this->subcomponents[$cix] = $component;
        return TRUE;
      }
      elseif( $argType == $subcomponent->objName ) {
        if( $index == $cix2sC ) {
          $this->subcomponents[$cix] = $component;
          return TRUE;
        }
        $cix2sC++;
      }
      elseif( !$argType && ($arg1 == $subcomponent2->getProperty( 'uid' ))) {
        $this->subcomponents[$cix] = $component;
        return TRUE;
      }
    }
            /* not found.. . insert anyway.. .*/
    $this->subcomponents[] = $component;
  }
/**
 * creates formatted output for subcomponents
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.22 - 2007-04-07
 * @return string
 */
  function createSubComponent() {
    $subcomponents = null;
    foreach( $this->subcomponents as $component ) {
      if( '' >= $component->getConfig( 'language'))
        $component->setConfig( 'language',  $this->getConfig( 'language' ));
      $component->setConfig( 'nl',          $this->getConfig( 'nl' ));
      $component->setConfig( 'unique_id',   $this->getConfig( 'unique_id' ));
      $component->setConfig( 'format',      $this->getConfig( 'format' ));
      $subcomponents .= $component->createComponent( $this->xcaldecl );
    }
    return $subcomponents;
  }
/********************************************************************************/
/**
 * break lines at pos 75
 *
 * Lines of text SHOULD NOT be longer than 75 octets, excluding the line
 * break. Long content lines SHOULD be split into a multiple line
 * representations using a line "folding" technique. That is, a long
 * line can be split between any two characters by inserting a CRLF
 * immediately followed by a single linear white space character (i.e.,
 * SPACE, US-ASCII decimal 32 or HTAB, US-ASCII decimal 9). Any sequence
 * of CRLF followed immediately by a single linear white space character
 * is ignored (i.e., removed) when processing the content type.
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @param string $value
 * @return string
 */
  function _size75( $string ) {
    $strlen = strlen( $string );
    $tmp    = $string;
    $string = null;
    while( $strlen > 75 ) {
      $string .= substr( $tmp, 0, 75 );
      $string .= $this->nl;
      $tmp     = ' '.substr( $tmp, 75 );
      $strlen  = strlen( $tmp );
    }
    $string .= rtrim( $tmp ); // the rest
    if( $this->nl != substr( $string, ( 0 - strlen( $this->nl ))))
      $string .= $this->nl;
    return $string;
  }
/**
 * special characters management output
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.12 - 2006-12-21
 * @param string $string
 * @return string
 */
  function _strrep( $string ) {
    switch( $this->format ) {
      case 'xcal':
        $string = htmlspecialchars( strip_tags( stripslashes( urldecode ( $string ))));
        break;
      default:
        $string = str_replace('"',        "'",    $string);
        $string = str_replace('\\',       '\\\\', $string);
        $string = str_replace(',',        '\,',   $string);
        $string = str_replace(';',        '\;',   $string);
        $string = str_replace( $this->nl, '\n',   $string);
        break;
    }
    return $string;
  }
/**
 * special characters management input (from iCal file)
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 1.x.x - 2007-04-27
 * @param string $string
 * @return string
 */
  function _strunrep( $string ) {
    $string = str_replace( '\\\\', '\\',     $string);
    $string = str_replace( '\,',   ',',      $string);
    $string = str_replace( '\;',   ';',      $string);
    $string = str_replace( '\n',  $this->nl, $string); // ??
    return $string;
  }
}
/*********************************************************************************/
/*********************************************************************************/
/**
 * class for calendar component VEVENT
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 */
class vevent extends calendarComponent {
/**
 * constructor for calendar component VEVENT object
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @return void
 */
  function vevent() {
    $this->calendarComponent();
  }
/**
 * create formatted output for calendar component VEVENT object instance
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 1.x.x - 2007-05-24
 * @param array $xcaldecl
 * @return string
 */
  function createComponent( &$xcaldecl ) {
    $this->_createFormat();
    $component     = $this->componentStart1.$this->objName.$this->componentStart2.$this->nl;
    $component    .= $this->createAttach();
    $component    .= $this->createAttendee();
    $component    .= $this->createCategories();
    $component    .= $this->createComment();
    $component    .= $this->createContact();
    $component    .= $this->createClass();
    $component    .= $this->createCreated();
    $component    .= $this->createDescription();
    $component    .= $this->createDtend();
    $component    .= $this->createDtstamp();
    $component    .= $this->createDtstart();
    $component    .= $this->createDuration();
    $component    .= $this->createExdate();
    $component    .= $this->createExrule();
    $component    .= $this->createGeo();
    $component    .= $this->createLastModified();
    $component    .= $this->createLocation();
    $component    .= $this->createOrganizer();
    $component    .= $this->createPriority();
    $component    .= $this->createRdate();
    $component    .= $this->createRelatedTo();
    $component    .= $this->createRequestStatus();
    $component    .= $this->createRecurrenceid();
    $component    .= $this->createResources();
    $component    .= $this->createRrule();
    $component    .= $this->createSequence();
    $component    .= $this->createStatus();
    $component    .= $this->createSummary();
    $component    .= $this->createTransp();
    $component    .= $this->createUid();
    $component    .= $this->createUrl();
    $component    .= $this->createXprop();
    if( $this->nl != substr( $component, ( 0 - strlen( $this->nl ))))
      $component  .= $this->nl;
    $component    .= $this->createSubComponent();
    $component    .= $this->componentEnd1.$this->objName.$this->componentEnd2;
    if( is_array( $this->xcaldecl ) && ( 0 < count( $this->xcaldecl ))) {
      foreach( $this->xcaldecl as $localxcaldecl )
        $xcaldecl[] = $localxcaldecl;
    }
    return $component;
  }
}
/*********************************************************************************/
/*********************************************************************************/
/**
 * class for calendar component VTODO
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 */
class vtodo extends calendarComponent {
/**
 * constructor for calendar component VTODO object
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @return void
 */
  function vtodo() {
    $this->calendarComponent();
  }
/**
 * create formatted output for calendar component VTODO object instance
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 1.x.x - 2007-05-24
 * @param array $xcaldecl
 * @return string
 */
  function createComponent( &$xcaldecl ) {
    $this->_createFormat();
    $component     = $this->componentStart1.$this->objName.$this->componentStart2.$this->nl;
    $component    .= $this->createAttach();
    $component    .= $this->createAttendee();
    $component    .= $this->createCategories();
    $component    .= $this->createClass();
    $component    .= $this->createComment();
    $component    .= $this->createCompleted();
    $component    .= $this->createContact();
    $component    .= $this->createCreated();
    $component    .= $this->createDescription();
    $component    .= $this->createDtstamp();
    $component    .= $this->createDtstart();
    $component    .= $this->createDue();
    $component    .= $this->createDuration();
    $component    .= $this->createExdate();
    $component    .= $this->createExrule();
    $component    .= $this->createGeo();
    $component    .= $this->createLastModified();
    $component    .= $this->createLocation();
    $component    .= $this->createOrganizer();
    $component    .= $this->createPercentComplete();
    $component    .= $this->createPriority();
    $component    .= $this->createRdate();
    $component    .= $this->createRelatedTo();
    $component    .= $this->createRequestStatus();
    $component    .= $this->createRecurrenceid();
    $component    .= $this->createResources();
    $component    .= $this->createRrule();
    $component    .= $this->createSequence();
    $component    .= $this->createStatus();
    $component    .= $this->createSummary();
    $component    .= $this->createUid();
    $component    .= $this->createUrl();
    $component    .= $this->createXprop();
    if( $this->nl != substr( $component, ( 0 - strlen( $this->nl ))))
      $component  .= $this->nl;
    $component    .= $this->createSubComponent();
    $component    .= $this->componentEnd1.$this->objName.$this->componentEnd2;
    if( is_array( $this->xcaldecl ) && ( 0 < count( $this->xcaldecl ))) {
      foreach( $this->xcaldecl as $localxcaldecl )
        $xcaldecl[] = $localxcaldecl;
    }
    return $component;
  }
}
/*********************************************************************************/
/*********************************************************************************/
/**
 * class for calendar component VJOURNAL
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 */
class vjournal extends calendarComponent {
/**
 * constructor for calendar component VJOURNAL object
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @return void
 */
  function vjournal() {
    $this->calendarComponent();
  }
/**
 * create formatted output for calendar component VJOURNAL object instance
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 1.x.x - 2007-05-24
 * @param array $xcaldecl
 * @return string
 */
  function createComponent( &$xcaldecl ) {
    $this->_createFormat();
    $component  = $this->componentStart1.$this->objName.$this->componentStart2.$this->nl;
    $component .= $this->createAttach();
    $component .= $this->createAttendee();
    $component .= $this->createCategories();
    $component .= $this->createClass();
    $component .= $this->createComment();
    $component .= $this->createContact();
    $component .= $this->createCreated();
    $component .= $this->createDescription();
    $component .= $this->createDtstamp();
    $component .= $this->createDtstart();
    $component .= $this->createExdate();
    $component .= $this->createExrule();
    $component .= $this->createLastModified();
    $component .= $this->createOrganizer();
    $component .= $this->createRdate();
    $component .= $this->createRequestStatus();
    $component .= $this->createRecurrenceid();
    $component .= $this->createRelatedTo();
    $component .= $this->createRrule();
    $component .= $this->createSequence();
    $component .= $this->createStatus();
    $component .= $this->createSummary();
    $component .= $this->createUid();
    $component .= $this->createUrl();
    $component .= $this->createXprop();
    $component .= $this->componentEnd1.$this->objName.$this->componentEnd2;
    if( is_array( $this->xcaldecl ) && ( 0 < count( $this->xcaldecl ))) {
      foreach( $this->xcaldecl as $localxcaldecl )
        $xcaldecl[] = $localxcaldecl;
    }
    return $component;
  }
}
/*********************************************************************************/
/*********************************************************************************/
/**
 * class for calendar component VFREEBUSY
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.7.3 - 2006-09-09
 */
class vfreebusy extends calendarComponent {
/**
 * constructor for calendar component VFREEBUSY object
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.7.3 - 2006-09-09
 * @return void
 */
  function vfreebusy() {
    $this->calendarComponent();
  }
/**
 * create formatted output for calendar component VFREEBUSY object instance
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.7 - 2006-11-20
 * @param array $xcaldecl
 * @return string
 */
  function createComponent( &$xcaldecl ) {
    $this->_createFormat();
    $component  = $this->componentStart1.$this->objName.$this->componentStart2.$this->nl;
    $component .= $this->createAttendee();
    $component .= $this->createComment();
    $component .= $this->createContact();
    $component .= $this->createDtend();
    $component .= $this->createDtstart();
    $component .= $this->createDtstamp();
    $component .= $this->createDuration();
    $component .= $this->createFreebusy();
    $component .= $this->createOrganizer();
    $component .= $this->createRequestStatus();
    $component .= $this->createUid();
    $component .= $this->createUrl();
    $component .= $this->createXprop();
    $component .= $this->componentEnd1.$this->objName.$this->componentEnd2;
    if( is_array( $this->xcaldecl ) && ( 0 < count( $this->xcaldecl ))) {
      foreach( $this->xcaldecl as $localxcaldecl )
        $xcaldecl[] = $localxcaldecl;
    }
    return $component;
  }
}
/*********************************************************************************/
/*********************************************************************************/
/**
 * class for calendar component VALARM
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 */
class valarm extends calendarComponent {
/**
 * constructor for calendar component VALARM object
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @return void
 */
  function valarm() {
    $this->calendarComponent();
  }
/**
 * create formatted output for calendar component VALARM object instance
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.10 - 2006-12-13
 * @param array $xcaldecl
 * @return string
 */
  function createComponent( &$xcaldecl ) {
    $this->_createFormat();
    $component     = $this->componentStart1.$this->objName.$this->componentStart2.$this->nl;
    $component    .= $this->createAction();
    $component    .= $this->createAttach();
    $component    .= $this->createAttendee();
    $component    .= $this->createDescription();
    $component    .= $this->createDuration();
    $component    .= $this->createRepeat();
    $component    .= $this->createSummary();
    $component    .= $this->createTrigger();
    $component    .= $this->createXprop();
    $component    .= $this->componentEnd1.$this->objName.$this->componentEnd2;
    if( is_array( $this->xcaldecl ) && ( 0 < count( $this->xcaldecl ))) {
      foreach( $this->xcaldecl as $localxcaldecl )
        $xcaldecl[] = $localxcaldecl;
    }
    return $component;
  }
}
/**********************************************************************************
/*********************************************************************************/
/**
 * class for calendar component VTIMEZONE
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-13
 */
class vtimezone extends calendarComponent {
  var $timezonetype;
/**
 * constructor for calendar component VTIMEZONE object
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-13
 * @param string $timezonetype optional, default FALSE ( STANDARD / DAYLIGHT )
 * @return void
 */
  function vtimezone( $timezonetype=FALSE ) {
    $this->calendarComponent();
    if( !$timezonetype )
      $this->timezonetype = 'VTIMEZONE';
    else
      $this->timezonetype = strtoupper( $timezonetype );
  }
/**
 * create formatted output for calendar component VTIMEZONE object instance
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.9.7 - 2006-11-20
 * @param array $xcaldecl
 * @return string
 */
  function createComponent( &$xcaldecl ) {
    $this->_createFormat();
    $component     = $this->componentStart1.$this->objName.$this->componentStart2.$this->nl;
    $component    .= $this->createTzid();
    $component    .= $this->createLastModified();
    $component    .= $this->createTzurl();
    $component    .= $this->createDtstart( TRUE );
    $component    .= $this->createTzoffsetfrom();
    $component    .= $this->createTzoffsetto();
    $component    .= $this->createComment();
    $component    .= $this->createRdate( TRUE );
    $component    .= $this->createRrule();
    $component    .= $this->createTzname();
    $component    .= $this->createXprop();
    if( $this->nl != substr( $component, ( 0 - strlen( $this->nl ))))
      $component  .= $this->nl;
    $component    .= $this->createSubComponent();
    $component    .= $this->componentEnd1.$this->objName.$this->componentEnd2;
    if( is_array( $this->xcaldecl ) && ( 0 < count( $this->xcaldecl ))) {
      foreach( $this->xcaldecl as $localxcaldecl )
        $xcaldecl[] = $localxcaldecl;
    }
    return $component;
  }
}
?>