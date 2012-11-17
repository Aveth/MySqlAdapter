<?php

include('path/to/MySqlAdapter');  //or use an autoloader

$mysql = new MySqlAdapter('localhost', 'mydb', 'root', 'password');  //instatiate the object

//create an insert command using named parameters
$mysql->set_insert_command('INSERT INTO users (email, password) VALUES (:email, :pass)');
 
//create a select command using unnamed parameters
$mysql->set_select_command('SELECT * FROM users WHERE id = ?');

//create an update command using unnamed parameters
$mysql->set_update_command('UPDATE users SET email = ? WHERE id = ?');

//create a delete command using named parameters
$mysql->set_delete_command('DELETE FROM users WHERE id = :id');


//set up paramaters for insert
$params = array(
    ':email' => 'email@example.com',
    ':pass' => some_hash_function('secretphrase')
);
$mysql->insert($params);  //execute the insert command

$id = $mysql->get_last_insert_id();  //get the insert id
$results = $mysql->select($id);  //execute the select command
print_r($results);  //show results

$mysql->update(array('newemail@example.com', $id));  //executes the update command

$mysql->delete(array(':id' => $id));  //executes the delete command

//execute a different query; the last command identifies it as a select query and will return results
$results = $mysql->execute('SELECT * FROM users WHERE email = ?', 'newemail@example.com', true);