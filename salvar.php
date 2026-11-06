<?php

require_once(dirname(__FILE__) . "\classes\\rb-mysql.php");
R::setup('mysql:host=localhost;dbname=todolist', 'root', 'aluno');


// Cadastrar ou editar lista
if (isset($_POST["nomeLista"]) && isset($_POST["idLista"])) {
    $nome = trim($_POST["nomeLista"]);
    $id = $_POST["idLista"];

    if ($nome != '') {

        // Atualiza uma lista existente
        if ($id != -1) {
            $lista = R::load('listas', $id);
        }

        // Cadastra uma nova lista
        else {
            $lista = R::dispense('listas');
        }

        $lista->nome = $nome;

        R::store($lista);
    }
}


// Cadastrar ou editar tarefa
else if (isset($_POST["desc"]) && isset($_POST["id"]) && isset($_POST["lista_id"])) {

    $descricao = filter_input(INPUT_POST, 'desc', FILTER_SANITIZE_SPECIAL_CHARS);
    $id = $_POST["id"];
    $listaId = $_POST["lista_id"];

    // Recupera o filtro e a pesquisa
    $filtro = $_POST["filtro"] ?? 'ordem';
    $buscaTarefas = $_POST["buscarTarefas"] ?? '';

    // Busca a lista à qual a tarefa pertence
    $lista = R::load('listas', $listaId);

    if ($lista->id && trim($descricao) != '') {

        // Atualiza uma tarefa existente
        if ($id != -1) {
            $tarefa = R::load('tarefas', $id);
        }

        // Cadastra uma nova tarefa
        else {
            $tarefa = R::dispense('tarefas');
            $tarefa->concluida = false;
        }

        $tarefa->descricao = $descricao;

        // Associa a tarefa à lista
        $tarefa->lista = $lista;

        R::store($tarefa);
    }

    // Retorna mantendo o filtro e a pesquisa
    $parametros = http_build_query([
        'lista' => $listaId,
        'filtro' => $filtro,
        'buscarTarefas' => $buscaTarefas
    ]);

    header("Location: index.php?" . $parametros . "#listaTarefas");
    exit;
}


// Atualizar estado da tarefa
else if (isset($_POST["estado"]) && isset($_POST["id"])) {

    $id = $_POST['id'];
    $estado = $_POST['estado'];
    $listaId = $_POST['lista_id'];

    // Recupera o filtro e a pesquisa
    $filtro = $_POST["filtro"] ?? 'ordem';
    $buscaTarefas = $_POST["buscarTarefas"] ?? '';

    $tarefa = R::load('tarefas', $id);
    $tarefa->concluida = $estado;

    R::store($tarefa);

    // Retorna mantendo o filtro e a pesquisa
    $parametros = http_build_query([
        'lista' => $listaId,
        'filtro' => $filtro,
        'buscarTarefas' => $buscaTarefas
    ]);

    header("Location: index.php?" . $parametros . "#listaTarefas");
    exit;
}


header("Location: index.php");
exit;