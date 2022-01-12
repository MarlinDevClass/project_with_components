<?php
namespace App;
use PDO;
use Aura\SqlQuery\QueryFactory;

class QueryBuilder{
    private $db;
    private $queryFactory;
    
    public function __construct(PDO $pdo, QueryFactory $queryFactory){//при создании класса нужно передать объект инициализации базы данных
        $this->db = $pdo;
        $this->queryFactory = $queryFactory;
    }

    public function selectAll($table){//метод выборки всех элементов из указанной таблицы
        $select = $this->queryFactory->newSelect();
        $select->cols(['*'])->from($table);
        $statement = $this->db->prepare($select->getStatement());
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);//получаем данные в виде массива и возвращаем его
    }
    
    public function selectDefinite($table, $where1){//метод выборки определённого(ых) элемента(ов) из указанной таблицы
        $select = $this->queryFactory->newSelect();
        $select->cols(['*'])->from($table);
        foreach($where1[0] as $key => $el){
            $select->where($el);
        }
        foreach($where1[1] as $key => $el){
            $select->bindValue($key, $el);
        }
        $statement = $this->db->prepare($select->getStatement());
        $statement->execute($select->getBindValues());
        return $statement->fetch(PDO::FETCH_ASSOC);//получаем данные в виде массива и возвращаем его
    }

    public function insert($table, array $data){//метод вставки строки в таблицу
        //параметрами принимаем название таблицы и даныне полей в виде массива - (название поля => значение)
        $insert = $this->queryFactory->newInsert();
        $insert->into($table)->cols($data);
        $statement = $this->db->prepare($insert->getStatement());
        $statement->execute($data);//выполняем вставку
    }

    public function update($table, array $data, array $where1){//метод обновления данных таблицы
        $update = $this->queryFactory->newUpdate();
        $update->table($table)->cols($data);
        foreach($where1[0] as $key => $el){
            $update->where($el);
        }
        foreach($where1[1] as $key => $el){
            $update->bindValue($key, $el);
        }
        $statement = $this->db->prepare($update->getStatement());
        $statement->execute($update->getBindValues());//выполняем запрос
    }

    public function delete($table, array $where1){//метод удаления строки из таблицы
        $delete = $this->queryFactory->newDelete();
        $delete->from($table);
        foreach($where1[0] as $key => $el){
            $delete->where($el);
        }
        foreach($where1[1] as $key => $el){
            $delete->bindValue($key, $el);
        }
        $statement = $this->db->prepare($delete->getStatement());
        $statement->execute($delete->getBindValues());//выполняем запрос
    }
}
?>