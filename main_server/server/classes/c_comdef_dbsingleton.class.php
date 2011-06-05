<?php

defined( 'BMLT_EXEC' ) or die ( 'Cannot Execute Directly' );	// Makes sure that this file is in the correct context.

/**
	\file c_comdef_dbsingleton.class.php
	
	\version 1.0
	
	\brief Wrapper class for a MySQL PDO object
	
	Singleton class accessed via static methods
	
	Based on code written by C. Drozdowski
	
	Call to c_comdef_dbsingleton::init() to populate connection params must be made BEFORE
	any attempts to connect to or query a database.
	
	c_comdef_dbsingleton::connect() may be called to explicitly connect to database (though
	not required- see next statement).
	
	Both c_comdef_dbsingleton::preparedQuery() and c_comdef_dbsingleton::preparedExec() allow
	"lazy loading" of the connection. That is, they'll try to connect if there isn't
	already a connection made via c_comdef_dbsingleton:connect() method.
	
	Typical usage:
	
	\code
		c_comdef_dbsingleton::init('host', 'database', 'user', 'password');
		c_comdef_dbsingleton::connect();
		$array = c_comdef_dbsingleton::preparedQuery('SELECT * FROM foo WHERE bar = :bar', array(':bar' => 'baz'));
	\endcode
	
	Lazy loading usage:
	
	\code
		c_comdef_dbsingleton::init('host', 'database', 'user', 'password');
		$array = c_comdef_dbsingleton::preparedQuery('SELECT * FROM foo WHERE bar = :bar', array(':bar' => 'baz'));
	\endcode

	Adding connection charset:

	\code
		c_comdef_dbsingleton::init('host', 'database', 'user', 'password', 'latin1');
	\endcode
	
	Internal instance of PDO is available via c_comdef_dbsingleton::pdoInstance() method.
	
	See PDO documentation for more info about connections and prepared statements
    
    This file is part of the Basic Meeting List Toolbox (BMLT).
    
    Find out more at: http://magshare.org/bmlt

    BMLT is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    BMLT is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this code.  If not, see <http://www.gnu.org/licenses/>.
 */
/// \brief This class provides a genericized interface to the <a href="http://us.php.net/pdo">PHP PDO</a> toolkit. It is a completely static class.
class c_comdef_dbsingleton
{
	/// \brief Internal PDO object
	private static $pdo = null;

	/// \brief Connection database driver type param
	private static $driver = null;

	/// \brief Connection host param
	private static $host = null;

	/// \brief Connection database param
	private static $database = null;

	/// \brief Connection user param
	private static $user = null;

	/// \brief Connection password param
	private static $password = null;

	/// \brief Connection charset param
	private static $charset = null;

	/// \brief Default fetch mode for internal PDOStatements
	private static $fetchMode = PDO::FETCH_ASSOC;

	/**
		\brief Private constructor (prevents direct creation of object)
	*/
	private function __construct()
	{
	}

	/**
		\brief Initializes connection param class members.
		
		Must be called BEFORE any attempts to connect to or query a database.
		
		Will destroy previous connection (if one exists).
	*/
	public static function init(
								$driver,			///< database server type (ex: 'mysql')
								$host,				///< database server host
								$database,			///< database name
								$user = null,		///< user, optional
								$password = null,	///< password, optional
								$charset = null		///< connection charset, optional
								)
	{
		if (self::$pdo instanceof pdo)
			{
			self::$pdo = null;
			}

		self::$driver = (string) $driver;
		self::$host = (string) $host;
		self::$database = (string) $database;

		if (!is_null($user))
			{
			self::$user = (string) $user;
			}

		if (!is_null($password))
			{
			self::$password = (string) $password;
			}

		if (!is_null($charset))
			{
			self::$charset = $charset;
			}
	}


	/**
		\brief Create internal PDO object thus connecting to database using connection
		param class members (passed in from previous call to c_comdef_dbsingleton::init())
		
		Will destroy previous connection (if one exists) before reconnecting
		
		\throws Exception	 thrown if internal PDO object cannot be created
									e.g. wrong connection param(s)
	*/
	public static function connect()
	{
		if (self::$pdo instanceof pdo)
			{
			self::$pdo = null;
			}

		try
			{
			$dsn = self::$driver . ':host=' . self::$host . ';dbname=' . self::$database;
			self::$pdo = new PDO($dsn, self::$user, self::$password);
			self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			self::$pdo->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);
			self::$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
			if (strlen(self::$charset) > 0)
				{
				self::preparedExec('SET NAMES :charset', array(':charset' => self::$charset));
				}

			/// For security purposes, once we connect, we get rid of all the info, and a new init needs to be called.

			self::$driver = null;
			self::$host = null;
			self::$database = null;
			self::$user = null;
			self::$password = null;
			self::$charset = null;
			}
		catch (PDOException $exception)
			{
			throw new Exception(__METHOD__ . '() ' . $exception->getMessage());
			}
	}

	/**
		\brief Returns whether internal PDO object is instantiated and thus connected
		
		\returns true if connected.
	*/
	public static function isConnected()
	{
		if (self::$pdo instanceof PDO)
			{
			return true;
			}
		else
			{
			return false;
			}
	}

	/**
		\brief Provides access to internal PDO object in case this classes functionality is not enough
		
		\returns the PDO object
		
		\throws Exception	 thrown if internal PDO object not instantiated
	*/
	public static function pdoInstance(
									$do_connect = false ///< Set this to true to force a connection if one does not yet exist. Default is false.
									)
	{
		if ( !self::isConnected() && $do_connect )
			{
			self::connect();
			}
			
		if (self::$pdo instanceof PDO)
			{
			return self::$pdo;
			}
		else
			{
			throw new Exception(__METHOD__ . '() internal PDO object not instantiated');
			}
	}

	/**
		\brief Wrapper for preparing and executing a PDOStatement that returns a resultset
		e.g. SELECT SQL statements.

		Returns a multidimensional array depending on internal fetch mode setting (self::$fetchMode)
		See PDO documentation about prepared queries.

		If there isn't already a database connection, it will "lazy load" the connection.

		Fetching key pairs- when $fetchKeyPair is set to TRUE, it will force the returned
		array to be a one-dimensional array indexed on the first column in the query.
		Note- query may contain only two columns or an exception/error is thrown.
		See PDO::PDO::FETCH_KEY_PAIR for more details

		\returns associative array of results.
		\throws Exception	 thrown if internal PDO exception is thrown
	*/
	public static function preparedQuery(
										$sql,					///< same as kind provided to PDO::prepare()
										$params = array(),		///< same as kind provided to PDO::prepare()
										$fetchKeyPair = false	///< See description in method documentation
										)

	{
		if (!self::$pdo instanceof PDO)
			{
			self::connect();
			}

		try
			{
			$stmt = self::$pdo->prepare($sql);
			$stmt->setFetchMode(self::$fetchMode);
			$stmt->execute($params);

			if ($fetchKeyPair)
				{
				return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
				}
			else
				{
				return $stmt->fetchAll();
				}
			}
		catch (PDOException $exception)
			{
			throw new Exception(__METHOD__ . '() ' . $exception->getMessage());
			}
	}

	/**
		\brief Wrapper for preparing and executing a PDOStatement that does not return a resultset
		e.g. INSERT or UPDATE SQL statements

		See PDO documentation about prepared queries.
		
		If there isn't already a database connection, it will "lazy load" the connection.
		
		\throws Exception	 thrown if internal PDO exception is thrown
		\returns true if execution is successful.
	*/
	public static function preparedExec(
										$sql,				///< same as kind provided to PDO::prepare()
										$params = array()	///< same as kind provided to PDO::prepare()
										)
	{
		if (!self::$pdo instanceof PDO)
			{
			self::connect();
			}

		try
			{
			$stmt = self::$pdo->prepare($sql);

			return $stmt->execute($params);
			}
		catch (PDOException $exception)
			{
			throw new Exception(__METHOD__ . '() ' . $exception->getMessage());
			}
	}

	/**
		\brief Wrapper for PDO::lastInsertId()
		
		\returns the ID of the last INSERT
		\throws Exception	 thrown if internal PDO object not instantiated
	*/
	public static function lastInsertId()
	{
		if (!self::$pdo instanceof PDO)
			{
			throw new Exception(__METHOD__ . '() internal PDO object not instantiated');
			}

		return self::$pdo->lastInsertId();

	}
};

?>
