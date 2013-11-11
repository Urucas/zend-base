<?php

require_once APPLICATION_PATH . '/models/Db.php';

class Model_Categorias extends Model_Db {
	
	protected $_name = 'categorias';
	protected $_primary = 'id';
        protected $_order	=	'nombre';
	
    public function getCategorias(){

        $table = new Zend_Db_Table('categorias');

        $sql = $table->select()
                        ->from($table,array('*'));

        $r = $table->fetchAll($sql);

        return $r->toArray();

    }

    public function getOneCategoria($id_categ){

		if(is_null($id_categ))
			return false;

		$where = "id = ".$id_categ;

        $table = new Zend_Db_Table('categorias');

        $sql = $table->select()
                        ->from($table,array('*'))
						->where($where);

        $r = $table->fetchAll($sql);

        return $r->toArray();

    }

	public function getCategoriasxLocal($idLocal){

		$table = new Zend_Db_Table("local_categoria");
		$sql = $table->select()->where("id_local = ".$idLocal);
		$r = $table->fetchAll($sql);
		return $r->toArray();
	}

    public function getCategoriasByLocal( $idLocal ){


		$where = "id IN ( SELECT id_categoria FROM local_categoria WHERE id_local = $idLocal ) ";
		$sql = $this->select()->where( $where );

        $r = $this->fetchAll($sql);
        return $r->toArray();

    }
}
