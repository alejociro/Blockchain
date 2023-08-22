<?php

class User {
    public $name;
    public $money;
    public $hash;

    public function __construct($name, $money) {
        $this->name = $name;
        $this->money = $money;
        $this->hash = md5($name . $money);
    }
}

function transferMoney($userA, $userB, $money) {
    if ($userA->money < $money) {
        return "El usuario A no tiene suficiente dinero para la transferencia.";
    }

    if ($userA->hash != md5($userA->name . $userA->money) || $userB->hash != md5($userB->name . $userB->money)) {
        return "Error en la verificación de los usuarios.";
    }

    $userA->money -= $money;
    $userB->money += $money;

    $userA->hash = md5($userA->name . $userA->money);
    $userB->hash = md5($userB->name . $userB->money);

    return "Transferencia exitosa de $$money de {$userA->name} a {$userB->name}.";
}

$userA = new User("UsuarioA", 100);
$userB = new User("UsuarioB", 50);

echo transferMoney($userA, $userB, 30);
?>
