<?php
    require_once(dirname(__FILE__) . "\classes\\rb-mysql.php");
    R::setup('mysql:host=localhost;dbname=todolist', 'root', 'aluno');

    #region Tarefas

    $id = -1;
    $descricao = '';

    if (isset($_GET['editar'])) {
        $tarefa = R::load('tarefas', $_GET['editar']);

        if ($tarefa->id) {
            $id = $tarefa->id;
            $descricao = $tarefa->descricao;
        }
    }

    #endregion Tarefas

    #region Listas de tarefas

    $idLista = -1;
    $nomeLista = '';

    if (isset($_GET['editarLista'])) {
        $lista = R::load('listas', $_GET['editarLista']);

        if ($lista->id) {
            $idLista = $lista->id;
            $nomeLista = $lista->nome;
        }
    }

    #endregion Listas de tarefas

    // Identifica a lista selecionada
    $idListaAtual = $_GET['lista'] ?? ($id != -1 ? $tarefa->lista_id : -1);

    $listaAtual = R::load('listas', $idListaAtual);
    $nomeListaAtual = $listaAtual->nome;

    // Identifica o filtro selecionado
    $filtro = $_GET['filtro'] ?? 'ordem';

    if (!in_array($filtro, ['ordem', 'concluidas', 'nao_concluidas'])) {
        $filtro = 'ordem';
    }

    // Mantém a pesquisa atual
    $buscaTarefas = $_GET['buscarTarefas'] ?? '';
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To-Do list</title>

    <script src="https://kit.fontawesome.com/6c18a3221a.js" crossorigin="anonymous"></script>

    <link rel="stylesheet" href="styles\styles.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Antic+Didone&display=swap" rel="stylesheet">
</head>

<script>
    function atualizarEstado(checkbox) {
        checkbox.form.querySelector('input[name="estado"]').value =
            checkbox.checked ? 1 : 0;

        checkbox.form.submit();
    }

    function abrirFiltro(event) {
        event.stopPropagation();

        document.querySelector('.menu-filtro').classList.toggle('ativo');
    }

    document.addEventListener('click', function(event) {
        const filtro = document.querySelector('.filtro-container');

        if (!filtro.contains(event.target)) {
            document.querySelector('.menu-filtro').classList.remove('ativo');
        }
    });
</script>

<body>

    <!-- Parte das listas -->
    <div class="card-listas">

        <!-- Buscar listas -->
        <form action="" method="get" class="pesquisar">
            <i class="fa-solid fa-magnifying-glass"></i>

            <input
                type="text"
                name="buscarlistas"
                id="buscarlistas"
                placeholder="Buscar listas..."
                value="<?= htmlspecialchars($_GET['buscarlistas'] ?? '') ?>"
            >
        </form>

        <!-- Cabeçalho -->
        <section class="cabecalho">
            <p>Listas</p>

            <ul>
                <li>
                    <a href="#novaLista">
                        <i class="fa-solid fa-circle-plus"></i>
                    </a>
                </li>
            </ul>
        </section>

        <!-- Listas -->
        <section class="lista">

            <?php
                $modeloLista =
                '<div class="lista-item lista-item-clique">
                    <a href="?lista=%s#listaTarefas">
                        <p>%s</p>
                    </a>

                    <ul>
                        <li>
                            <a href="?editarLista=%s#novaLista">
                                <i class="fa-solid fa-pencil"></i>
                            </a>
                        </li>
                        <li>
                            <a href="excluir.php?tipo=lista&id=%s">
                                <i class="fa-solid fa-trash-can"></i>
                            </a>
                        </li>
                    </ul>
                </div>';

                $busca = $_GET['buscarlistas'] ?? '';

                if (!empty($busca)) {
                    $listas = R::getAll(
                        'SELECT * FROM listas WHERE nome LIKE ?',
                        ['%' . $busca . '%']
                    );
                } else {
                    $listas = R::getAll('SELECT * FROM listas');
                }

                if (!empty($listas)) {
                    foreach ($listas as $lista) {
                        printf(
                            $modeloLista,
                            $lista['id'],
                            htmlspecialchars($lista['nome']),
                            $lista['id'],
                            $lista['id']
                        );
                    }
                }
            ?>

        </section>
    </div>


    <!-- Parte da lista de tarefas -->
    <div class="card-tarefas <?= $idListaAtual == -1 ? 'lista-escondida' : '' ?>" id="listaTarefas">

        <!-- Buscar tarefas -->
        <form action="" method="get" class="pesquisar">

            <i class="fa-solid fa-magnifying-glass"></i>

            <input type="hidden" name="lista" value="<?= $idListaAtual ?>">

            <input type="hidden" name="filtro" value="<?= htmlspecialchars($filtro) ?>">

            <input
                type="text"
                name="buscarTarefas"
                id="buscarTarefas"
                placeholder="Buscar tarefas..."
                value="<?= htmlspecialchars($buscaTarefas) ?>"
            >
        </form>

        <!-- Cabeçalho -->
        <section class="cabecalho">
            <p><?= htmlspecialchars($nomeListaAtual ?: 'Nome') ?></p>

            <ul>
                <li>
                    <a href="#novaTarefa">
                        <i class="fa-solid fa-circle-plus"></i>
                    </a>
                </li>

                <!-- Filtro -->
                <li class="filtro-container">
                    <button type="button" class="btn-filtro" onclick="abrirFiltro(event)">
                        <i class="fa-solid fa-filter"></i>
                    </button>

                    <div class="menu-filtro">

                        <a href="?lista=<?= $idListaAtual ?>&filtro=ordem&buscarTarefas=<?= urlencode($buscaTarefas) ?>#listaTarefas">
                            Ordem de criação
                        </a>

                        <a href="?lista=<?= $idListaAtual ?>&filtro=concluidas&buscarTarefas=<?= urlencode($buscaTarefas) ?>#listaTarefas">
                            Apenas concluídas
                        </a>

                        <a href="?lista=<?= $idListaAtual ?>&filtro=nao_concluidas&buscarTarefas=<?= urlencode($buscaTarefas) ?>#listaTarefas">
                            Apenas não concluídas
                        </a>

                    </div>
                </li>
            </ul>
        </section>

        <!-- Lista de tarefas -->
        <section class="lista">

            <?php
                $modelo =
                '<div class="lista-item">
                    <form method="post" action="salvar.php">
                        <div class="tarefa">
                            <input type="hidden" name="estado" value="0">

                            <label class="checkbox">
                                <input type="checkbox" onchange="atualizarEstado(this)" %s>
                                <span class="checkbox-box"></span>
                            </label>

                            <input type="hidden" name="id" value="%s">
                            <input type="hidden" name="lista_id" value="%s">
                            <input type="hidden" name="filtro" value="%s">
                            <input type="hidden" name="buscarTarefas" value="%s">

                            <span class="texto-selecionado">%s</span>
                        </div>
                    </form>

                    <ul>
                        %s
                        <li>
                            <a href="excluir.php?tipo=tarefa&id=%s&lista_id=%s">
                                <i class="fa-solid fa-trash-can"></i>
                            </a>
                        </li>
                    </ul>
                </div>';

                if ($idListaAtual != -1) {

                    $sql = 'SELECT * FROM tarefas WHERE lista_id = ?';
                    $parametros = [$idListaAtual];

                    // Pesquisa pelo nome da tarefa
                    if (!empty($buscaTarefas)) {
                        $sql .= ' AND descricao LIKE ?';
                        $parametros[] = '%' . $buscaTarefas . '%';
                    }

                    // Aplica o filtro selecionado
                    if ($filtro == 'concluidas') {
                        $sql .= ' AND concluida = 1';
                    } elseif ($filtro == 'nao_concluidas') {
                        $sql .= ' AND concluida = 0';
                    }

                    // Ordem de criação
                    $sql .= ' ORDER BY id ASC';

                    $tarefas = R::getAll($sql, $parametros);

                    if (!empty($tarefas)) {
                        foreach ($tarefas as $tarefa) {

                            // Exibe a opção de editar apenas se a tarefa não estiver concluída
                            $editar = $tarefa['concluida'] == 0
                                ? '<li><a href="?editar=' . $tarefa['id'] . '&lista=' . $idListaAtual . '&filtro=' . $filtro . '&buscarTarefas=' . urlencode($buscaTarefas) . '#novaTarefa"><i class="fa-solid fa-pencil"></i></a></li>'
                                : '';

                            printf(
                                $modelo,
                                $tarefa['concluida'] == 1 ? 'checked' : '',
                                $tarefa['id'],
                                $idListaAtual,
                                htmlspecialchars($filtro),
                                htmlspecialchars($buscaTarefas),
                                htmlspecialchars($tarefa['descricao']),
                                $editar,
                                $tarefa['id'],
                                $idListaAtual
                            );
                        }
                    }
                }
            ?>

        </section>
    </div>


    <!-- Modal Nova Lista -->
    <div id="novaLista" class="janelaNovaTarefa">

        <form method="post" action="salvar.php" class="janelaNovaTarefa-card">

            <label for="nomeLista">Nome da lista</label>

            <input
                type="text"
                name="nomeLista"
                id="nomeLista"
                placeholder="Digite um nome para a lista."
                value="<?= htmlspecialchars($nomeLista) ?>"
                required
            >

            <input type="hidden" name="idLista" value="<?= $idLista ?>">

            <div class="btns">
                <a href="#" class="btn-fechar">Cancelar</a>
                <input class="btn-salvareentrar" type="submit" value="Salvar">
            </div>

        </form>
    </div>


    <!-- Modal Nova Tarefa / Editar Tarefa -->
    <div id="novaTarefa" class="janelaNovaTarefa">

        <form method="post" action="salvar.php" class="janelaNovaTarefa-card">

            <label for="desc">Descrição</label>

            <input
                type="text"
                name="desc"
                id="desc"
                placeholder="Digite um texto aqui."
                value="<?= htmlspecialchars($descricao) ?>"
                required
            >

            <input type="hidden" name="id" value="<?= $id ?>">
            <input type="hidden" name="lista_id" value="<?= $idListaAtual ?>">
            <input type="hidden" name="filtro" value="<?= htmlspecialchars($filtro) ?>">
            <input type="hidden" name="buscarTarefas" value="<?= htmlspecialchars($buscaTarefas) ?>">

            <div class="btns">
                <a href="#listaTarefas" class="btn-fechar">Cancelar</a>
                <input class="btn-salvareentrar" type="submit" value="Salvar">
            </div>

        </form>
    </div>

</body>
</html>