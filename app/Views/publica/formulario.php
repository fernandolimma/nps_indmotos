<?php
/**
 * @var array $cliente
 * @var array $perguntas
 * @var string $csrf_token
 * @var string|null $logo_url
 */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesquisa de Satisfação - <?= App\Core\View::escape(App\Core\View::appName()) ?></title>
    <link rel="icon" type="image/png" sizes="192x192" href="<?= App\Core\Router::baseUrl('/imagens/nps_indmotos_192x192.png') ?>">
    <link rel="icon" type="image/png" sizes="500x500" href="<?= App\Core\Router::baseUrl('/imagens/nps_indmotos_500x500.png') ?>">
    <link rel="apple-touch-icon" sizes="192x192" href="<?= App\Core\Router::baseUrl('/imagens/nps_indmotos_192x192.png') ?>">
    <meta name="theme-color" content="#f3f5f7">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= App\Core\Router::baseUrl('/css/nps-theme.css') ?>" rel="stylesheet">
    <style>
        .survey-shell {
            max-width: 840px;
            margin: 3rem auto;
        }

        .survey-intro {
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .survey-logo {
            max-width: 100%;
            max-height: 90px;
            margin-bottom: 1.5rem;
        }

        .survey-intro h1 {
            font-size: clamp(1.6rem, 4vw, 2.15rem);
            font-weight: 700;
            letter-spacing: -0.035em;
        }

        .survey-intro p {
            color: #64737c;
            margin-bottom: 0;
        }

        .survey-summary,
        .survey-question {
            background: #fff;
            border: 1px solid #e8edf0;
            border-radius: 0.85rem;
            box-shadow: 0 0.35rem 1.25rem rgba(32, 54, 74, 0.08);
        }

        .survey-summary {
            padding: 1.25rem 1.5rem;
            margin-bottom: 1.5rem;
        }

        .survey-summary h2 {
            font-size: 0.85rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            margin-bottom: 1rem;
        }

        .survey-details {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.7rem 1.5rem;
            margin: 0;
            font-size: 0.9rem;
        }

        .survey-details dt {
            color: #72808a;
            font-weight: 500;
        }

        .survey-details dd {
            margin: 0;
            overflow-wrap: anywhere;
        }

        .survey-question {
            padding: 1.6rem;
            margin: 0 0 1.1rem;
            min-width: 0;
        }

        .survey-question-title {
            float: none;
            width: 100%;
            margin: 0 0 1.3rem;
            color: #304651;
            font-size: 1.05rem;
            font-weight: 600;
            line-height: 1.5;
        }

        .survey-question fieldset {
            min-width: 0;
            padding: 0;
            margin: 0;
            border: 0;
        }

        .survey-score-title {
            text-align: center;
        }

        .survey-scores {
            display: grid;
            grid-template-columns: repeat(11, minmax(0, 1fr));
            gap: 0.45rem;
        }

        .survey-score {
            position: relative;
            display: flex;
            justify-content: center;
        }

        .survey-score input {
            position: absolute;
            width: 1px;
            height: 1px;
            opacity: 0;
        }

        .survey-score label {
            display: grid;
            place-items: center;
            width: min(100%, 3rem);
            aspect-ratio: 1;
            border: 2px solid #b9c4ca;
            border-radius: 50%;
            color: #617681;
            cursor: pointer;
            font-weight: 600;
            transition: background-color 0.15s, border-color 0.15s, color 0.15s;
        }

        .survey-score label:hover {
            border-color: #536873;
            background: #f0f4f5;
        }

        .survey-score input:checked + label {
            color: #fff;
            background: #53646c;
            border-color: #53646c;
        }

        .survey-score input:focus-visible + label {
            outline: 3px solid #397f75;
            outline-offset: 3px;
        }

        .survey-scale {
            display: grid;
            grid-template-columns: 7fr 2fr 2fr;
            gap: 0.35rem;
            margin-top: 0.65rem;
        }

        .survey-scale span {
            height: 4px;
            border-radius: 3px;
        }

        .survey-scale span:nth-child(1),
        .survey-score--low::after { background: #d54d53; }
        .survey-scale span:nth-child(2),
        .survey-score--middle::after { background: #e8c635; }
        .survey-scale span:nth-child(3),
        .survey-score--high::after { background: #65c58b; }

        .survey-question textarea {
            resize: vertical;
        }

        .survey-submit {
            width: 100%;
            padding: 0.85rem;
            border-radius: 0.7rem;
            font-weight: 650;
        }

        @media (max-width: 767.98px) {
            .survey-shell {
                margin: 1.5rem auto;
            }

            .survey-summary,
            .survey-question {
                padding: 1.15rem;
            }

            .survey-details {
                gap: 0.75rem;
            }

            .survey-scores {
                grid-template-columns: repeat(6, minmax(0, 1fr));
                gap: 0.9rem 0.35rem;
            }

            .survey-score {
                padding-bottom: 0.5rem;
            }

            .survey-score::after {
                content: "";
                position: absolute;
                right: 0;
                bottom: 0;
                left: 0;
                height: 4px;
                border-radius: 3px;
            }

            .survey-scale {
                display: none;
            }
        }

        @media (max-width: 419.98px) {
            .survey-scores {
                grid-template-columns: repeat(5, minmax(0, 1fr));
            }
        }

        @media (max-width: 359.98px) {
            .survey-scores {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
        }
    </style>
</head>
<body class="nps-public-page">
    <main class="container survey-shell">
        <header class="survey-intro">
            <?php if (!empty($logo_url)): ?>
                <img class="survey-logo" src="<?= App\Core\View::escape($logo_url) ?>" alt="Logo">
            <?php endif; ?>
            <h1>Pesquisa de Satisfação</h1>
            <p>Sua opinião é muito importante para nós.</p>
        </header>

        <section class="survey-summary" aria-labelledby="survey-summary-title">
            <h2 id="survey-summary-title">Dados da OS</h2>
            <dl class="survey-details">
                <div><dt>Cliente</dt><dd><?= App\Core\View::escape($cliente['nome_cliente']) ?></dd></div>
                <div><dt>Atendimento ID</dt><dd><?= (int) $cliente['id'] ?></dd></div>
                <div><dt>Veículo</dt><dd><?= App\Core\View::escape($cliente['modelo_veiculo']) ?></dd></div>
                <div><dt>Tipo OS</dt><dd><?= App\Core\View::escape($cliente['tipo_os']) ?></dd></div>
                <div><dt>Número OS</dt><dd><?= App\Core\View::escape($cliente['numero_os']) ?></dd></div>
                <div><dt>Data Conclusão</dt><dd><?= $cliente['data_conclusao'] ? date('d/m/Y', strtotime($cliente['data_conclusao'])) : '' ?></dd></div>
            </dl>
        </section>

        <form action="<?= App\Core\Router::baseUrl('/pesquisa/submit') ?>" method="POST">
            <input type="hidden" name="csrf_token" value="<?= App\Core\View::escape($csrf_token) ?>">
            <input type="hidden" name="token" value="<?= App\Core\View::escape($cliente['token_unico']) ?>">

            <?php foreach ($perguntas as $pergunta): ?>
                <?php if ($pergunta['tipo'] === 'escala_0_10'): ?>
                    <div class="survey-question">
                        <fieldset>
                            <legend class="survey-question-title survey-score-title"><?= App\Core\View::escape($pergunta['texto_pergunta']) ?></legend>
                            <div class="survey-scores">
                                <?php for ($i = 0; $i <= 10; $i++): ?>
                                    <div class="survey-score survey-score--<?= $i <= 6 ? 'low' : ($i <= 8 ? 'middle' : 'high') ?>">
                                        <input type="radio" name="pergunta_<?= $pergunta['id'] ?>" id="pergunta_<?= $pergunta['id'] ?>_<?= $i ?>" value="<?= $i ?>" required>
                                        <label for="pergunta_<?= $pergunta['id'] ?>_<?= $i ?>"><?= $i ?></label>
                                    </div>
                                <?php endfor; ?>
                            </div>
                            <div class="survey-scale" aria-hidden="true"><span></span><span></span><span></span></div>
                        </fieldset>
                    </div>
                <?php else: ?>
                    <div class="survey-question">
                        <label class="survey-question-title" for="pergunta_<?= $pergunta['id'] ?>"><?= App\Core\View::escape($pergunta['texto_pergunta']) ?></label>
                        <textarea class="form-control" id="pergunta_<?= $pergunta['id'] ?>" name="pergunta_<?= $pergunta['id'] ?>" rows="4" placeholder="Sua resposta..."></textarea>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>

            <button type="submit" class="btn btn-primary survey-submit">Enviar Resposta</button>
        </form>
    </main>
</body>
</html>
