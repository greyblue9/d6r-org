<?php

define("MYSQL_NUM", MYSQLI_NUM);
define("MYSQL_ASSOC", MYSQLI_ASSOC);
define("MYSQL_BOTH", MYSQLI_BOTH);

require_once('include/session.php');

class DB
{
	// DB connection resource (link-identifier)
	/** @var mysqli|null */
    private static $linki = null; // link for mysqli (prepared statements) extension
	/** @var int|null */
	private static $lastNumRows = null;



	// ------- Compatibility functions ---------

	private static $mysqlResultId = 0;

	private static $mysqlNumRowsByResultId = array();
	private static $mysqlDataByResultId = array();
	private static $mysqlIdxByResultId = array();

	static function mysql_error($link_identifier = null) {
		$errorText = mysqli_error(self::$linki);
		return $errorText;
	}

	static function mysql_query($query, $link_identifier = null) {

		self::$mysqlResultId++;
		$result = self::$mysqlResultId;

		self::$mysqlDataByResultId[$result] = DB::execute($query);
		self::$mysqlNumRowsByResultId[$result] = DB::getNumRows();
		self::$mysqlIdxByResultId[$result] = 0;

		return $result;
	}

	static function mysql_num_rows($result) {
		return self::$mysqlNumRowsByResultId[$result];
	}

	static function mysql_affected_rows($link_identifier = null) {
		return DB::getNumAffectedRows();
	}

	static function mysql_fetch_array($result, $result_type = MYSQL_BOTH) {
		$idx = self::$mysqlIdxByResultId[$result];
		$totalRows = self::$mysqlNumRowsByResultId[$result];

		if ($idx === $totalRows) return null;

		$rowAssoc = self::$mysqlDataByResultId[$result][$idx];

		$rowAssocAndNum = array_merge($rowAssoc);

		$rowIndex = 0;
		foreach ($rowAssoc as $column => $val) {
			$rowAssocAndNum[$rowIndex] = $val;
			$rowIndex++;
		}

		self::$mysqlIdxByResultId[$result]++;
		return $rowAssocAndNum;
	}

	static function mysql_fetch_row($result) {
		$idx = self::$mysqlIdxByResultId[$result];
		$totalRows = self::$mysqlNumRowsByResultId[$result];

		if ($idx === $totalRows) return null;

		$rowAssoc = self::$mysqlDataByResultId[$result][$idx];

		$rowNumeric = array();

		$rowIndex = 0;
		foreach ($rowAssoc as $column => $val) {
			$rowNumeric[$rowIndex] = $val;
			$rowIndex++;
		}

		self::$mysqlIdxByResultId[$result]++;
		return $rowNumeric;
	}

	static function mysql_fetch_assoc($result) {
		$idx = self::$mysqlIdxByResultId[$result];
		$totalRows = self::$mysqlNumRowsByResultId[$result];

		if ($idx === $totalRows) return null;
		$rowAssoc = self::$mysqlDataByResultId[$result][$idx];

		self::$mysqlIdxByResultId[$result]++;
		return $rowAssoc;
	}

	static function mysql_insert_id($link_identifier = null) {
		return DB::safeQueryInsertId();
	}

	static function mysql_data_seek($result, $row_number) {
		self::$mysqlIdxByResultId[$result] = $row_number;
	}

	static function Query($query, $link_identifier = null)
	{
		return self::mysql_query($query, $link_identifier = null);
	}
	// -----------------------------------




	static function handleMysqlError($errorText, $query = null, $params = null)
	{
		ob_end_clean();

		if (!headers_sent()) {
			header('Content-Type: text/plain;charset=utf-8');
		}

		print(
			"\n------------ MYSQL ERROR --------------\n\n".
			$errorText."\n\n".
			"\n---------------------------------------\n\n"
		);

		if ($query !== null) {
			print("\n\n -- Query: -- \n");
			print_r($query);
		}

		if ($query !== null) {
			print("\n\n -- Parameters: -- \n");
			print_r($params);
		}

		flush();
		exit();

	}


	static function connect()
	{

		$session = Session::get();
		switch ($session->environment) {
			case Session::ENVIRONMENT_OLDTIMBERWAY:
				$mysqlHost = '192.168.1.106';
				break;
			default:
				$mysqlHost = '127.0.0.1';
				break;
		}

		// new-style connection (mysqli, param-based)
		/*self::$linki = @mysqli_connect(
			$mysqlHost,
			"mothgu2_main",
			"dobson",
			"mothgu2_fishcontest"
		);*/

		// new-style connection (mysqli, param-based)
		self::$linki = @mysqli_connect(
			$mysqlHost,
			//"pinproje_main",
			//"Neptune558!",
			//"pinproje_fishcontest"
			"mothgu2_main",
			"dobson",
			"mothgu2_fishcontest"
		);

		if (mysqli_connect_errno() !== 0) {
			self::handleMysqlError(mysqli_connect_error());
		}
	}


	private static function checkWarnings() {
		if (self::$linki->warning_count) {
			$warnings = self::$linki->get_warnings();
			$warningsStr = "WARNINGS:\n\n".print_r($warnings, true);
			self::handleMysqlError($warningsStr);
		}
	}


	/**
	 * Needed for mysqli 'bind_param' function used with 'call_user_func_array'.
	 *
	 * @param array $arr
	 * @return array
	 */
	private static function refValues($arr)
	{
		if (strnatcmp(phpversion(),'5.3') >= 0)
		{
			// Reference is required for PHP 5.3+
			$refs = array();
			foreach($arr as $key => $value) {
				$refs[$key] = &$arr[$key];
			}
			return $refs;
		}

		// PHP < 5.3
		return $arr;
	}

    /**
     * @param string $queryStr Query string with ? where params go
     * @param array $args Parameter values
     *
     * @return array Query result
     */
    static function execute($queryStr, $args = array()) {

	    self::$lastNumRows = null;

        $typesString = '';
        foreach ($args as $arg) {
            $typeCharacter = ''; // data type character for mysqli; one of "i" (int), "d" (double), "s", or "b" (blob)
            switch (gettype($arg)) {
                case "boolean":
                    $typeCharacter = 'i';
                    break;
                case "integer":
                    $typeCharacter = 'i';
                    break;
                case "double":
                    $typeCharacter = 'd';
                    break;
                case "string":
                    $typeCharacter = 's';
                    break;
                case "array":
                    $typeCharacter = 's';
                    break;
                case "object":
                    $typeCharacter = 's';
                    break;
                case "resource":
                    $typeCharacter = 's';
                    break;
                case "NULL":
                    $typeCharacter = 's';
                    break;
                case "unknown type":
                    $typeCharacter = 's';
                    break;
                default:
                    $typeCharacter = 'b';
                    break;
            }
            $typesString .= $typeCharacter;
        }

	    /* prepare query */
		$stmt = mysqli_prepare(self::$linki, $queryStr);

	    if ($stmt === false) {
		    self::handleMysqlError('DB::execute - mysqli_prepare error: <br/>'.htmlspecialchars(self::$linki->error), $queryStr, $args);
	    }

	    if (count($args)) {
	        /* bind parameters for markers */
	        $bind_param_result = call_user_func_array(array($stmt, "bind_param"), array_merge(array($typesString), self::refValues($args)));
		    if ($bind_param_result === false) {
			    self::handleMysqlError('DB::execute - mysqli_bind_param error: <br/>'.htmlspecialchars($stmt->error), $queryStr, $args);
		    }
	    }

        /* execute query */
        $exec_result = mysqli_stmt_execute($stmt);
	    if ($exec_result === false) {
		    self::handleMysqlError('DB::execute - mysqli_stmt_execute error: <br/>'.htmlspecialchars($stmt->error), $queryStr, $args);
	    }

		/** @var mysqli_result $metadata */
        $metadata = $stmt->result_metadata();
	    if ($metadata === false) {
			// probably insert?
			// DBR 2013-10-08 TODO: Check on usage of mysqli for insert, update (non-query)
		    mysqli_stmt_close($stmt);
		    //die('Mysqli metadata === false');
			return false;
		}

		// Create a row array where each row maps column names to values
	    $data = array();
		$fields = $metadata->fetch_fields();

		$rowCount = 0;
		while (true)
		{
			$pointers = array();
			$row = array();

			$pointers[] = $stmt;
			foreach ($fields as $field)
			{
				$fieldname = $field->name;
				$pointers[] = &$row[$fieldname];
			}

			call_user_func_array('mysqli_stmt_bind_result', $pointers);

			if (!$stmt->fetch()) {
				break;
			}

			$data[] = $row;
			$rowCount++;
		}

	    self::$lastNumRows = $rowCount;

		$metadata->free();

        /* close statement */
        mysqli_stmt_close($stmt);

        return $data;
    }


    /**
     * @param string $queryStr Query string with ? where params go
     * @param array $args Parameter values
     *
     * @return array|null Single row, if found
     */
    static function getSingleRow($queryStr, $args = array()) {
	    $data = self::execute($queryStr, $args);

	    if (count($data) === 1) {
		    return $data[0];
	    } else if (count($data) === 0) {
		    return null;
	    }

	    self::handleMysqlError("getSingleRow() - More than one row ".
				"returned!", $queryStr, $args);
	    return null;
    }

	static function get($objectType, $params) {

		$tablename = '';
		switch ($objectType) {
			case 'user':
				$tablename = 'fishcontest_users';
				break;
			case 'fish':
				$tablename = 'fish1';
				break;
			case 'contest':
				$tablename = 'fishcontest_contests';
				break;
			default:
				throw new Exception('DB::get() -- Unknown object type: '.$objectType);
		}

		foreach ($params as $column => $value) {
			$predicate .= "AND $column = ? ";
		}

		$query = "
			SELECT
				*
			FROM
				$tablename
			WHERE
				1 = 1
				$predicate
		";

	}

	static function realEscapeString($str) {
		return mysqli_real_escape_string(self::$linki, $str);
	}

	/**
	 * FIXME: This is not user-friendly
	 * @return mixed
	 */
	static function safeQueryInsertId() {
		return self::$linki->insert_id;
	}

	/**
	 * Compatibility function to fetch multiple (or zero) rows using a SQL
	 * "SELECT"-type query. Using DB::execute() is preferred.
	 *
	 * @param string $pQueryStr
	 * @return array An (ordered) array containing the set of rows, where
	 *      each row is an (associative) array mapping column-names to row
	 *      values.
	 */
	static function dataQuery($pQueryStr)
	{
		return self::execute($pQueryStr, array());
	}

	/**
	 * Compatibility function to fetch single row using a SQL "SELECT"-type
	 * query. Returns NULL if result yields no rows.
	 *
	 * @param string $pQueryStr
	 * @return array|null|false An (associative) array mapping column-names to
	 *      row values; if no rows, NULL. If multiple rows, FALSE (and the
	 *      query needs to be fixed to never return more than a single
	 *      well-defined row).
	 */
    static function singleQuery($pQueryStr)
    {
        $data = self::execute($pQueryStr, array());

        if (count($data) === 1) {
	        // Exactly one matching row
            return $data[0];

        } else if (count($data) > 0) {

	        // More than one matching row (undefined behavior; add order+limit)
	        self::handleMysqlError(
		        "DB::singleQuery error -- Result set matched multiple rows; \n".
	            "undefined behavior; stopping.\n\n".
		        "Query:\n".
		        $pQueryStr."\n\n".
		        "Result (as JSON):\n".
		        json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
	        );

	        return false;

        } else {
	        // No matching row
	        return null;
        }
    }

	static function getNumRows()
	{
		return self::$lastNumRows;
	}

	static function getNumAffectedRows()
	{
		return mysqli_affected_rows(self::$linki);
	}

	/**
	 * TODO: Implement function for mysqli (if applicable)
	 *
	 * @param int $pRowNumber
	 * @param bool $pResult
	 * @return bool
	 */
	static function seekTo($pRowNumber, $pResult = false)
	{
		self::handleMysqlError(
			"DB::seekTo -- Not supported yet."
		);

		return false;
	}

	/**
	 * TODO: Implement function for mysqli (if applicable)
	 *
	 * @param bool $pResult
	 * @return bool
	 */
	static function rewind($pResult = false)
	{
		return self::seekTo(0, $pResult);
	}

	/**
	 * FIXME: Why would this be called with mysqli implementation?
	 */
	static function close()
	{
		mysqli_close(self::$linki);
	}

}



