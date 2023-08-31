<?php

class User {
    public $name;
    public $balance;

    public function __construct($name, $initialBalance) {
        $this->name = $name;
        $this->balance = $initialBalance;
    }

    public function deductAmount($amount) {
        $this->balance -= $amount;
    }

    public function addAmount($amount) {
        $this->balance += $amount;
    }
}

class Transaction {
    public $from;
    public $to;
    public $amount;

    public function __construct($from, $to, $amount) {
        $this->from = $from;
        $this->to = $to;
        $this->amount = $amount;
    }

    public function isValid() {
        if (is_null($this->from)) {
            // Coinbase transaction, always valid
            return true;
        } else {
            return $this->from->balance >= $this->amount && $this->amount > 0;
        }
    }

    public function execute() {
        if ($this->isValid()) {
            if (!is_null($this->from)) {
                $this->from->deductAmount($this->amount);
            }
            $this->to->addAmount($this->amount);
        } else {
            throw new Exception("Invalid Transaction!");
        }
    }

    public function getHash() {
        $fromName = is_null($this->from) ? "Coinbase" : $this->from->name;
        return hash('sha256', $fromName . $this->to->name . $this->amount);
    }
}

class BlockHeader {
    public $merkleRoot;
    public $prevHash;
    public $nonce;
    public $id;
    public $timestamp;
    public $numTransactions;

    public function __construct($prevHash) {
        $this->prevHash = $prevHash;
        $this->id = uniqid();
        $this->timestamp = time();
    }
}

class Block {
    public $header;
    public $transactions = [];

    public function __construct($prevHash = '') {
        $this->header = new BlockHeader($prevHash);
    }

    public function addTransaction($transaction) {
        if ($transaction->isValid()) {
            if(is_null($transaction->from)) {  // Si es una transacción coinbase, se pone al principio
                array_unshift($this->transactions, $transaction);
            } else {
                $this->transactions[] = $transaction;
            }
            $transaction->execute();
            $this->header->numTransactions = count($this->transactions);
        } else {
            throw new Exception("Transaction is invalid. Cannot add to block.");
        }
    }

    public function computeMerkleRoot() {
        $hashes = [];
        foreach ($this->transactions as $transaction) {
            $hashes[] = $transaction->getHash();
        }

        while (count($hashes) > 1) {
            $newHashes = [];
            while (!empty($hashes)) {
                $hash1 = array_shift($hashes);
                $hash2 = !empty($hashes) ? array_shift($hashes) : $hash1;
                $newHashes[] = hash('sha256', $hash1 . $hash2);
            }
            $hashes = $newHashes;
        }

        $this->header->merkleRoot = $hashes[0];
    }

    public function mine($difficulty) {
        $this->computeMerkleRoot();
        $target = str_repeat('0', $difficulty);
        $attempts = 0;
        while (substr($this->getBlockHash(), 0, $difficulty) !== $target) {
            $this->header->nonce++;
            $attempts++;
            if ($attempts % 1000 == 0) {  // Solo para no llenar la consola con demasiada información. Puedes ajustar este número.
                echo "Intento $attempts: Nonce: {$this->header->nonce} | Hash: {$this->getBlockHash()}\n";
            }
        }
        echo "Bloque minado después de $attempts intentos! Nonce final: {$this->header->nonce} | Hash del Bloque: {$this->getBlockHash()}\n";
    }

    public function getBlockHash() {
        return hash('sha256', $this->header->prevHash . $this->header->merkleRoot . $this->header->nonce);
    }
}

class Blockchain {
    public $blocks = [];
    public $difficulty;

    public function __construct($difficulty) {
        $this->difficulty = $difficulty;
    }

    public function addBlock($block) {
        $block->mine($this->difficulty);
        $this->blocks[] = $block;
    }

    public function getLastHash() {
        return empty($this->blocks) ? '' : end($this->blocks)->getBlockHash();
    }
}

// Demo
function main() {
    $difficulty = (int) readline("Introduce la dificultad (número de ceros iniciales del hash, entre 1 y 4): ");
    if ($difficulty < 1 || $difficulty > 4) {
        echo "Dificultad no válida.";
        return;
    }

    // Create Users
    $userA = new User("UsuarioA", 100);
    $userB = new User("UsuarioB", 50);
    $userC = new User("UsuarioC", 30);

    $users = [$userA, $userB, $userC];

    echo "\n\n----------- Estado Inicial de los usuarios -----------\n";
    foreach ($users as $user) {
        echo "Usuario: " . $user->name . " - Saldo: " . $user->balance . "\n";
    }

    // Initialize Blockchain
    $blockchain = new Blockchain($difficulty);

    // Create a block
    $block = new Block($blockchain->getLastHash());

    // Coinbase transaction
    $coinbase = new Transaction(null, $userA, 50);  // Reward to UsuarioA
    $block->addTransaction($coinbase);

    // Normal transactions
    $block->addTransaction(new Transaction($userA, $userB, 30));
    $block->addTransaction(new Transaction($userB, $userC, 10));

    // Add block to blockchain
    $blockchain->addBlock($block);

    foreach ($blockchain->blocks as $block) {
        echo "------ Block Header ------\n";
        echo "ID: " . $block->header->id . "\n";
        echo "Timestamp: " . date("Y-m-d H:i:s", $block->header->timestamp) . "\n";
        echo "Número de transacciones: " . $block->header->numTransactions . "\n";
        echo "Merkle Root: " . $block->header->merkleRoot . "\n";
        echo "Nonce: " . $block->header->nonce . "\n";
        echo "Hash del bloque: " . $block->getBlockHash() . "\n";

        echo "\n------ Block Body (Transacciones) ------\n";
        foreach ($block->transactions as $transaction) {
            $fromName = is_null($transaction->from) ? "Coinbase" : $transaction->from->name;
            echo "De: " . $fromName . " - A: " . $transaction->to->name . " - Monto: " . $transaction->amount . "\n";
        }

        echo "\n---------------------------------------------------------\n";
    }

    echo "\n\n----------- Estado Final de los usuarios -----------\n";
    foreach ($users as $user) {
        echo "Usuario: " . $user->name . " - Saldo: " . $user->balance . "\n";
    }
}

main();
?>

