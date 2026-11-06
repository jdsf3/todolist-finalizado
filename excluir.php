<?php

require_once(dirname(__FILE__) . "\classes\\rb-mysql.php");
R::setup('mysql:host=localhost;dbname=todolist', 'root', '');

$tipo = $_GET['tipo'] ?? '';
$id = (int)($_GET['id'] ?? 0);
$listaId = (int)($_GET['lista_id'] ?? 0);

// Excluir lista
if ($tipo == 'lista' && $id > 0) {
    $lista = R::load('listas', $id);

    if ($lista->id) {
        R::exec('DELETE FROM tarefas WHERE lista_id = ?', [$id]);
        R::trash($lista);
    }
}

// Excluir tarefa
else if ($tipo == 'tarefa' && $id > 0) {
    $tarefa = R::load('tarefas', $id);

    if ($tarefa->id && $tarefa->lista_id == $listaId) {
        R::trash($tarefa);
    }
}

// Retornar para a página
if ($tipo == 'tarefa' && $listaId > 0) {
    header("Location: index.php?lista=" . $listaId . "#listaTarefas");
} else {
    header("Location: index.php");
}

exit;