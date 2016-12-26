<?php

/*
 * Helper functions for building a DataTables server-side processing SQL query
 *
 * The static functions in this class are just helper functions to help build
 * the SQL used in the DataTables demo server-side processing scripts. These
 * functions obviously do not represent all that can be done with server-side
 * processing, they are intentionally simple to show how it works. More complex
 * server-side processing operations will likely require a custom script.
 *
 * See http://datatables.net/usage/server-side for full details on the server-
 * side processing requirements of DataTables.
 *
 * @license MIT - http://datatables.net/license_mit
 */

class SSP {
	/**
	 * Create the data output array for the DataTables rows
	 *
	 *  @param  array $columns Column information array
	 *  @param  array $data    Data from the SQL get
	 *  @return array          Formatted data in a row based format
	 */
	static function data_output ( $columns, $data )
	{
		$out = array();

		for ( $i=0, $ien=count($data) ; $i<$ien ; $i++ ) {
			$row = array();

			for ( $j=0, $jen=count($columns) ; $j<$jen ; $j++ ) {
				$column = $columns[$j];

				// Is there a formatter?
				if ( isset( $column['formatter'] ) ) {
					$row[ $column['dt'] ] = $column['formatter']( $data[$i][ $column['db'] ], $data[$i] );
				}
				else {
					$row[ $column['dt'] ] = $data[$i][ $columns[$j]['db'] ];
				}
			}

			$out[] = $row;
		}

		return $out;
	}



	/**
	 * Paging
	 *
	 * Construct the LIMIT clause for server-side processing SQL query
	 *
	 *  @param  array $request Data sent to server by DataTables
	 *  @return string SQL limit clause
	 */
	static function limit ( $request )
	{
		if ( isset($request['start']) && $request['start'] > 0 ) {
			return array('limit' => intval($request['length']), 'offset' =>intval($request['start'] ));
		}
		else
			return array('limit' => intval($request['length']), 'offset' => 0);
	}


	/**
	 * Ordering
	 *
	 * Construct the ORDER BY clause for server-side processing SQL query
	 *
	 *  @param  array $request Data sent to server by DataTables
	 *  @param  array $columns Column information array
	 *  @return string SQL order by clause
	 */
	static function order ( $request, $columns )
	{
		$orderBy = array();

		if ( isset($request['order']) && count($request['order']) ) {
			$orderBy = array();
			$dtColumns = self::pluck( $columns, 'dt' );

			for ( $i=0, $ien=count($request['order']) ; $i<$ien ; $i++ ) {
				// Convert the column index into the column data property
				$columnIdx = intval($request['order'][$i]['column']);
				$requestColumn = $request['columns'][$columnIdx];

				$columnIdx = array_search( $requestColumn['data'], $dtColumns );
				$column = $columns[ $columnIdx ];

				if ( $requestColumn['orderable'] == 'true' ) {
					$dir = $request['order'][$i]['dir'] === 'asc' ?
						'ASC' :
						'DESC';

					$orderBy[] = $column['db'].' '.$dir;
				}
			}
		}

		return $orderBy;
	}


	/**
	 * Searching / Filtering
	 *
	 * Construct the WHERE clause for server-side processing SQL query.
	 *
	 * NOTE this does not match the built-in DataTables filtering which does it
	 * word by word on any field. It's possible to do here performance on large
	 * databases would be very poor
	 *
	 *  @param  array $request Data sent to server by DataTables
	 *  @param  array $columns Column information array
	 *  @return string SQL where clause
	 */
	static function filter ( $request, $columns )
	{
		$globalSearch = array();
		$dtColumns = self::pluck( $columns, 'dt' );

		if ( isset($request['search']) && $request['search']['value'] != '' ) {
			$str = $request['search']['value'];

			for ( $i=0, $ien=count($request['columns']) ; $i<$ien ; $i++ ) {
				$requestColumn = $request['columns'][$i];
				$columnIdx = array_search( $requestColumn['data'], $dtColumns );
				$column = $columns[ $columnIdx ];

				if ( $requestColumn['searchable'] == 'true' && isset($column['is_search'])) {
					$globalSearch[$column['db']] = $str;
				}
			}
		}

		return $globalSearch;
	}

	/**
	 * Pull a particular property from each assoc. array in a numeric array, 
	 * returning and array of the property values from each item.
	 *
	 *  @param  array  $a    Array to get data from
	 *  @param  string $prop Property to read
	 *  @return array        Array of property values
	 */
	static function pluck ( $a, $prop )
	{
		$out = array();

		for ( $i=0, $len=count($a) ; $i<$len ; $i++ ) {
			$out[] = $a[$i][$prop];
		}

		return $out;
	}


	/**
	 * Return a string from an array or a string
	 *
	 * @param  array|string $a Array to join
	 * @param  string $join Glue for the concatenation
	 * @return string Joined string
	 */
	static function _flatten ( $a, $join = ' AND ' )
	{
		if ( ! $a ) {
			return '';
		}
		else if ( $a && is_array($a) ) {
			return implode( $join, $a );
		}
		return $a;
	}
        
        
        /**
         *  Return id of search string defined by file config or referent table 
         *  @param  array $request Data Integer sent to server by DataTables
	 *  @param  array $columns Column information array
	 *  @return string SQL where clause
         */
        static function filter_integer($request, $columns) {
            $globalSearch = array();
            $dtColumns = self::pluck( $columns, 'dt' );

            if ( isset($request['search']) && $request['search']['value'] != '' ) {
                $str = $request['search']['value'];

                for ( $i=0, $ien=count($request['columns']) ; $i<$ien ; $i++ ) {
                    $requestColumn = $request['columns'][$i];
                    $columnIdx = array_search( $requestColumn['data'], $dtColumns );
                    $column = $columns[ $columnIdx ];

                    if ( $requestColumn['searchable'] == 'true' && isset($column['int_search'])) {
                        $columnType = $column['int_search']['type'];
                        $columnName = $column['int_search']['name'];
                        $data = array();
                        //1. Using config file: config/app.yml
                        //2. Using foreign key in other table
                        if($columnType === 'config'){//1
                            $dataTmp = \sfConfig::get($columnName);
                            foreach ($dataTmp as $key => $value) {
                                if(preg_match("/".$str."/i", $value)){
                                    array_push($data, $key);
                                }
                            }
                            if(!empty($data)){
                                $globalSearch[$column['db']] = $data;
                            }
                        }elseif($columnType === 'foreign'){//2
                            $select = $column['int_search']['select'];
                            $where = $column['int_search']['where'];
                            $thisTable = $columnName::getInstance();
                            $dataTmp = $thisTable->createQuery('a')
                                    ->where('a.'.$where.' ILIKE ?', "%".$str."%")
                                    ->select('a.'.$select)
                                    ->execute();
                            foreach ($dataTmp as $value) {
                                array_push($data, $value->$select);
                            }
                            if(!empty($data)){
                                $globalSearch[$column['db']] = $data;
                            }
                        }
                    }
                }
            }
            return $globalSearch;
        }
}
