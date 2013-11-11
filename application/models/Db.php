<?php

class Model_Db extends Zend_Db_Table_Abstract {
	
	public function listar( $offset = null,$campos = "*", $where = null){
		
		ini_set("mysql.trace_mode", "0");

		$select = $this->select()
					->from( $this->_name, array( new Zend_Db_Expr( 'SQL_CALC_FOUND_ROWS '.$campos ) ) )
					->order( $this->_order );

		if($where) $select->where($where);

		if ( !is_null( $offset ) ) {
			$select->limit( $this->_limit, $offset * $this->_limit );
		}
		$rs 	= $this->fetchAll( $select );

		return $rs->toArray();

	}

        public function getTableFields( $table ){
    //        Devuelve el nombre de los campos de una tabla

//            $fields =   $this->_db->describeTable( $table );
            $fields = $this->info(Zend_Db_Table_Abstract::COLS);
            return $fields;

        }

        public function getFieldsDescription( $table ){

            $fields =   $this->_db->describeTable( $table );
            return $fields;

        }

	public function setOffset( $offset ){
		
		$this->_offset	=	$offset;
		
	}
	
	public function setLimit( $limit ){
		
		$this->_limit	=	$limit;
		
	}
	
	public function add( $data ){
		
		$this->insert( $data );
		return $this->_db->lastInsertId( $this->_name );

	}

	public function get( $where = null, $offset = null ){

		$select	=	$this->select()
					->from( $this->_name, array( new Zend_Db_Expr( 'SQL_CALC_FOUND_ROWS *' ) ) );
					
		if ( !is_null( $where ) ){
			
			if ( is_array( $where ) ){
				
				foreach ( $where as $key => $row )
					$select->where( $row );

			} else {

				$select->where( $where );

			}
			
		}

		if ( !is_null( $offset ) )
			$select->limitPage( $offset * $this->_limit, $offset );
//			$select->limit( $this->_limit, $offset * $this->_limit );
//echo $select;
		$rs 	= $this->fetchAll( $select );
			
		return $rs->toArray();

	}
	
	public function deleteByPrimaryKey( $id ){
//		Elimina un registro por su clave primaria

		$rs = $this->find( $id )->current();

		if ( !is_null( $rs ) ){
			
			$rs = $this->find( $id )->current()->delete();

		}
		
		return $rs;
		
	}
	
	public function actualizar( $data, $where ){

		$where = $this->getAdapter()->quoteInto( $this->_primary . ' = ?', $where );
		$this->update( $data, $where );
		
	}
	
	public function getRecordById( $id ){
		
		$rs = $this->find( $id );

		if ( !$rs )	return null;
		$rs	=	$rs->toArray();
		return sizeof($rs) ? $rs[0] : null;
		
	}
	
	public function guardar( $data, $where = null ){


		$id = ( isset ( $data['id'] ) ) ?   $data['id'] :   '';

/*                $tableDescription   =   $this->getFieldsDescription( $this->_name );
                foreach ( $tableDescription as $fieldName => $row ){
                    if( $row['DATA_TYPE'] == 'timestamp' ){

                        list( $day, $month, $year ) =   explode( '/', $data[ $fieldName ] );
                        $data[ $fieldName ] =   mktime(0, 0, 0, $month, $day, $year );

                    }
                }
*/
		if ( ( !isset( $data['id'] ) || empty( $data['id'] ) ) && is_null( $where ) ){
//			Si esta ingresando un registro nuevo

			$id = $this->add( $data );
			
		} else {
//			Si se esta guardando un registro existente
			$this->actualizar( $data, $where );

		}
		
		return $id;

	}
	
	  public function getCalcFoundRows()
    {
    	
    	$sql = "SELECT FOUND_ROWS() AS total";
    	$rs = $this->_db->fetchAll( $sql );
    	
    	return $rs[0]['total'];

    }

    public function execute( $sql ){

        $rs     =   $this->_db->query($sql );

//        $rs->setFetchMode( Zend_db::FETCH_ASSOC );
        return $rs->fetchAll();

    }

    public function getOrder(){

        return $this->_order;

    }

/*	
	private function paginar( $pages ){
		
		Zend_View_Helper_PaginationControl::setDefaultViewPartial('modules/paginador.phtml');
		
		$data = range( 1, $pages );
		$paginator = Zend_Paginator::factory( $data );
//		$paginator = Zend_Paginator::factory( $select );

		$paginator->setDefaultScrollingStyle('Sliding');
		
//		$paginator->setCurrentPageNumber( $this->_getParam('page' ) );
		$paginator->setDefaultItemCountPerPage( 2 );

		// Assign the Paginator object to the view
		Zend_View::assign( 'paginator', $paginator );

//		$this->view->paginator = $paginator;

	}
*/
}

?>
