
CREATE DATABASE IF NOT EXISTS nps_indmotos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE nps_indmotos;

CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    senha_hash VARCHAR(255) NOT NULL,
    tipo_usuario VARCHAR(20) NOT NULL DEFAULT 'USUARIO',
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS configuracoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    smtp_host VARCHAR(255) NOT NULL,
    smtp_port INT NOT NULL,
    smtp_usuario VARCHAR(255) NOT NULL,
    smtp_senha VARCHAR(255) NOT NULL,
    email_remetente VARCHAR(255) NOT NULL,
    nome_remetente VARCHAR(255) NOT NULL,
    logo_path VARCHAR(255) NULL,
    dias_validade INT NOT NULL DEFAULT 7,
    evolution_enabled BOOLEAN NOT NULL DEFAULT FALSE,
    evolution_url VARCHAR(500) NULL,
    evolution_instance VARCHAR(255) NULL,
    evolution_api_key TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO configuracoes (smtp_host, smtp_port, smtp_usuario, smtp_senha, email_remetente, nome_remetente, dias_validade)
VALUES ('smtp.gmail.com', 587, 'seu-email@gmail.com', 'sua-senha-app', 'seu-email@gmail.com', 'Indmotos', 7);

CREATE TABLE IF NOT EXISTS perguntas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    texto_pergunta TEXT NOT NULL,
    tipo ENUM('escala_0_10', 'discursiva') NOT NULL,
    ativa BOOLEAN NOT NULL DEFAULT TRUE,
    ordem INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO perguntas (texto_pergunta, tipo, ativa, ordem) VALUES
('Qual a probabilidade de você recomendar a Indmotos a um amigo ou conhecido?', 'escala_0_10', TRUE, 1),
('Como foi sua experiência com o atendimento?', 'discursiva', TRUE, 2);

CREATE TABLE IF NOT EXISTS clientes_os (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome_cliente VARCHAR(255) NOT NULL,
    email_cliente VARCHAR(255) NOT NULL,
    telefone_whatsapp VARCHAR(30) NULL,
    modelo_veiculo VARCHAR(255),
    tipo_os VARCHAR(255),
    numero_os VARCHAR(255) NOT NULL,
    data_conclusao DATE,
    status_resposta ENUM('pendente', 'respondido') NOT NULL DEFAULT 'pendente',
    status_email ENUM('nao_enviado', 'enviado', 'retornado_erro') NOT NULL DEFAULT 'nao_enviado',
    status_whatsapp ENUM('nao_enviado', 'enviado', 'retornado_erro', 'nao_disponivel') NOT NULL DEFAULT 'nao_disponivel',
    token_unico VARCHAR(255) NOT NULL UNIQUE,
    data_limite DATE NOT NULL,
    data_importacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS respostas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_os_id INT NOT NULL,
    pergunta_id INT NOT NULL,
    resposta_texto TEXT,
    nota INT CHECK (nota BETWEEN 0 AND 10),
    data_resposta TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_os_id) REFERENCES clientes_os(id) ON DELETE CASCADE,
    FOREIGN KEY (pergunta_id) REFERENCES perguntas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
