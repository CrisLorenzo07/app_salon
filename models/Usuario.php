<?php

namespace Model;

class Usuario extends ActiveRecord
{
    protected static $tabla = 'users';
    protected static $columnasDB = ['id', 'name', 'last_name', 'phone', 'email', 'password', 'admin', 'confirmed', 'token'];

    public ?int $id;
    public string $name;
    public string $last_name;
    public string $phone;
    public string $email;
    public string $password;
    public bool $admin;
    public bool $confirmed;
    public string $token;

    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? null;
        $this->name = $args['name'] ?? '';
        $this->last_name = $args['last_name'] ?? '';
        $this->phone = $args['phone'] ?? '';
        $this->email = $args['email'] ?? '';
        $this->password = $args['password'] ?? '';
        $this->admin = $args['admin'] ?? 0;
        $this->confirmed = $args['confirmed'] ?? 0;
        $this->token = $args['token'] ?? '';
    }

    public function validarNuevaCuenta(){
        if(!$this->name){
            self::$alertas['error'] [] = 'El nombre es obligatorio';
        }
                if(!$this->last_name){
            self::$alertas['error'] [] = 'El apellido es obligatorio';
        }
        return self::$alertas;
    }
}
