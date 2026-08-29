DROP DATABASE IF EXISTS db_contabi;
CREATE DATABASE db_contabi
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

USE db_contabi;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    cpf VARCHAR(14) NOT NULL UNIQUE,                    
    email VARCHAR(150) NULL,
    telefone VARCHAR(20) NULL,
    senha VARCHAR(255) NOT NULL,                        
    perfil ENUM('administrador', 'consultor', 'agente_pi') NOT NULL DEFAULT 'consultor',
    ativo BOOLEAN NOT NULL DEFAULT TRUE,
    criado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    alterado TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_cpf (cpf),
    INDEX idx_perfil (perfil)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,                            
    nome VARCHAR(200) NOT NULL,
    tipo_pessoa ENUM('PF', 'PJ') NOT NULL DEFAULT 'PJ',
    cpf_cnpj VARCHAR(20) NOT NULL,
    email VARCHAR(150) NULL,
    telefone VARCHAR(20) NULL,
    endereco TEXT NULL,
    observacoes TEXT NULL,
    criado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    alterado TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_clientes_usuario 
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) 
        ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_usuario_id (usuario_id),
    INDEX idx_cpf_cnpj (cpf_cnpj)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS marcas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    numero_processo VARCHAR(30) NOT NULL UNIQUE,        
    titular VARCHAR(200) NOT NULL,
    classe_nice INT NOT NULL,                           
    status VARCHAR(50) NOT NULL DEFAULT 'Em análise',   
    data_deposito DATE NOT NULL,
    data_concessao DATE NULL,
    data_vencimento DATE NULL,                          
    data_proxima_anuidade DATE NULL,
    data_renovacao DATE NULL,
    data_oposicao DATE NULL,
    data_prorrogacao DATE NULL,
    data_manifestacao DATE NULL,
    apresentacao ENUM('Nominal', 'Figurativa', 'Mista', 'Tridimensional', 'Posicional', 'Outro') NULL,
    observacoes TEXT NULL,
    criado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    alterado TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_marcas_cliente 
        FOREIGN KEY (cliente_id) REFERENCES clientes(id) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_numero_processo (numero_processo),
    INDEX idx_cliente_id (cliente_id),
    INDEX idx_classe_nice (classe_nice),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS patentes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    numero_processo VARCHAR(30) NOT NULL UNIQUE,
    titular VARCHAR(200) NOT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'Depositada',
    data_deposito DATE NOT NULL,
    data_concessao DATE NULL,
    data_vencimento DATE NULL,
    tipo_patente ENUM('Patente de Invenção', 'Modelo de Utilidade', 'Desenho Industrial', 'Outro') NULL,
    inventores TEXT NULL,
    resumo TEXT NULL,
    data_proxima_anuidade DATE NULL,
    data_manifestacao DATE NULL,
    data_prorrogacao DATE NULL,
    observacoes TEXT NULL,
    criado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    alterado TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_patentes_cliente 
        FOREIGN KEY (cliente_id) REFERENCES clientes(id) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_numero_processo (numero_processo),
    INDEX idx_cliente_id (cliente_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS prazos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    marca_id INT NULL,
    patente_id INT NULL,
    tipo ENUM('anuidade', 'renovacao_marca', 'oposicao', 'prorrogacao', 'manifestacao', 'vencimento', 'outro') NOT NULL,
    data_vencimento DATE NOT NULL,
    data_alerta_30 DATE NULL,
    data_alerta_15 DATE NULL,
    data_alerta_7 DATE NULL,
    notificado BOOLEAN NOT NULL DEFAULT FALSE,
    status ENUM('pendente', 'cumprido', 'vencido', 'cancelado') NOT NULL DEFAULT 'pendente',
    observacoes TEXT NULL,
    criado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    alterado TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_prazos_marca 
        FOREIGN KEY (marca_id) REFERENCES marcas(id) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_prazos_patente 
        FOREIGN KEY (patente_id) REFERENCES patentes(id) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_data_vencimento (data_vencimento),
    INDEX idx_status (status),
    INDEX idx_marca_id (marca_id),
    INDEX idx_patente_id (patente_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS despachos_rpi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_revista VARCHAR(20) NOT NULL,           
    data_publicacao DATE NOT NULL,
    numero_processo VARCHAR(30) NOT NULL,          
    codigo_despacho VARCHAR(20) NULL,
    descricao TEXT NULL,
    link TEXT NULL,
    marca_id INT NULL,                             
    patente_id INT NULL,
    processado BOOLEAN NOT NULL DEFAULT FALSE,
    criado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_numero_processo (numero_processo),
    INDEX idx_numero_revista (numero_revista),
    INDEX idx_data_publicacao (data_publicacao),
    UNIQUE KEY uk_despacho_rpi (numero_revista, numero_processo, codigo_despacho),
    CONSTRAINT fk_despachos_marca 
        FOREIGN KEY (marca_id) REFERENCES marcas(id) 
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_despachos_patente 
        FOREIGN KEY (patente_id) REFERENCES patentes(id) 
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS notificacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    tipo ENUM('prazo_critico', 'novo_despacho', 'colisao', 'sistema') NOT NULL,
    titulo VARCHAR(200) NOT NULL,
    mensagem TEXT NOT NULL,
    lida BOOLEAN NOT NULL DEFAULT FALSE,
    link TEXT NULL,                                
    criado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notificacoes_usuario 
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_usuario_id (usuario_id),
    INDEX idx_lida (lida),
    INDEX idx_criado (criado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS documentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    marca_id INT NULL,
    patente_id INT NULL,
    cliente_id INT NULL,
    nome_arquivo VARCHAR(255) NOT NULL,
    caminho_arquivo VARCHAR(500) NOT NULL,
    tipo ENUM('procuracao', 'comprovante', 'certificado', 'outro') NOT NULL DEFAULT 'outro',
    tamanho_bytes INT NULL,
    uploaded_by INT NULL,
    criado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    alterado TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_documentos_marca FOREIGN KEY (marca_id) REFERENCES marcas(id) ON DELETE CASCADE,
    CONSTRAINT fk_documentos_patente FOREIGN KEY (patente_id) REFERENCES patentes(id) ON DELETE CASCADE,
    CONSTRAINT fk_documentos_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    CONSTRAINT fk_documentos_usuario FOREIGN KEY (uploaded_by) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS logs_alteracoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tabela VARCHAR(50) NOT NULL,
    registro_id INT NOT NULL,
    usuario_id INT NULL,
    acao ENUM('INSERT', 'UPDATE', 'DELETE') NOT NULL,
    campo_alterado VARCHAR(100) NULL,
    valor_antigo TEXT NULL,
    valor_novo TEXT NULL,
    criado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tabela_registro (tabela, registro_id),
    INDEX idx_usuario_id (usuario_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE IF NOT EXISTS marcas_inpi (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    numero_processo VARCHAR(30) NOT NULL,
    nome_marca VARCHAR(255) NOT NULL,
    nome_normalizado VARCHAR(255) NOT NULL,
    chave_fonetica VARCHAR(255) NOT NULL,
    titular VARCHAR(500) NULL,
    classe_nice INT NOT NULL,
    status VARCHAR(100) NULL,
    apresentacao VARCHAR(100) NULL,
    data_deposito DATE NULL,
    numero_revista VARCHAR(20) NULL,
    atualizado TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_marcas_inpi_processo_classe (numero_processo, classe_nice),
    INDEX idx_marcas_inpi_classe (classe_nice),
    INDEX idx_marcas_inpi_nome (nome_normalizado),
    INDEX idx_marcas_inpi_fonetica (chave_fonetica),
    INDEX idx_marcas_inpi_revista (numero_revista)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS classes_nice (
    classe INT PRIMARY KEY,
    descricao TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Administrador (senha: Admin@123)*/
INSERT IGNORE INTO usuarios (nome, cpf, email, telefone, senha, perfil, ativo) VALUES
('Admin Contabi', '00000000000', 'admin@contabi.com', '(45) 99999-0000', 
 '$2y$12$lqQXW24gr1PPn7wDJxTDbuC/BDtRwzmEqzDdnosxrctexS7RHRJYy', 'administrador', TRUE);

/*Consultor (senha: Consultor@123)*/
INSERT IGNORE INTO usuarios (nome, cpf, email, telefone, senha, perfil, ativo) VALUES
('Enzo Feldman', '12345678901', 'enzo@contabi.com', '(45) 99988-7766', 
 '$2y$12$dRSiLHGuUW9V4uxNO6OyJ.g9tLedFWHgA75zv60gbCnHBElTsJbbe', 'consultor', TRUE);

INSERT INTO classes_nice (classe, descricao) VALUES
(1, 'Produtos químicos para uso industrial, científico e fotográfico, bem como para agricultura, horticultura e silvicultura; resinas artificiais não processadas, plásticos não processados; adubos; composições extintoras de incêndio; preparações para temperar e soldar metais; substâncias químicas para preservar alimentos; matérias tanantes; adesivos (colas) para uso industrial'),
(2, 'Tintas, vernizes, lacas; preservativos contra a ferrugem e contra a deterioração da madeira; corantes; mordentes; resinas naturais em estado bruto; metais em folhas e em pó para pintores, decoradores, impressores e artistas'),
(3, 'Preparações para branquear e outras substâncias para uso em lavanderia; produtos de limpeza, polimento, desengorduramento e abrasão; sabões; perfumaria, óleos essenciais, cosméticos, loções para os cabelos; dentifrícios'),
(4, 'Óleos e gorduras industriais; lubrificantes; composições para absorver, pulverizar e colocar poeira; combustíveis (incluindo gasolina para motores) e materiais de iluminação; velas e pavios para iluminação'),
(5, 'Produtos farmacêuticos, veterinários e sanitários; substâncias dietéticas para uso médico ou veterinário, alimentos para bebês; emplastros, material para pensos; material para obturações dentárias e para moldes dentários; desinfetantes; produtos para a destruição de parasitas nocivos; fungicidas, herbicidas'),
(6, 'Metais comuns e suas ligas; materiais de construção metálicos; construções transportáveis metálicas; materiais metálicos para vias férreas; cabos e fios metálicos não elétricos; serralharia e ferragens metálicas; canos e tubos metálicos; cofres; minérios'),
(7, 'Máquinas e máquinas-ferramentas; motores (exceto para veículos terrestres); acoplamentos e dispositivos de transmissão (exceto para veículos terrestres); instrumentos agrícolas; incubadoras de ovos'),
(8, 'Ferramentas e instrumentos manuais operados manualmente; cutelaria; garfos e colheres; armas brancas; navalhas'),
(9, 'Aparelhos e instrumentos científicos, náuticos, geodésicos, fotográficos, cinematográficos, ópticos, de pesagem, de medição, de sinalização, de controle (inspeção), de salvamento e de ensino; aparelhos para condução, distribuição, transformação, acumulação, regulação ou controle de eletricidade; aparelhos de gravação, transmissão ou reprodução de som ou imagens; suportes de gravação magnéticos, discos acústicos; distribuidores automáticos e mecanismos para aparelhos de pré-pagamento; caixas registradoras, máquinas de calcular, equipamentos de processamento de dados e computadores; extintores de incêndio'),
(10, 'Aparelhos e instrumentos cirúrgicos, médicos, odontológicos e veterinários, membros, olhos e dentes artificiais; artigos ortopédicos; material de sutura'),
(11, 'Aparelhos de iluminação, de aquecimento, de produção de vapor, de cozimento, de refrigeração, de secagem, de ventilação, de distribuição de água e instalações sanitárias'),
(12, 'Veículos; aparelhos de locomoção por terra, ar ou água'),
(13, 'Armas de fogo; munições e projéteis; explosivos; fogos de artifício'),
(14, 'Metais preciosos e suas ligas e produtos nessas matérias ou em chapados não compreendidos noutras classes; joalheria, bijuteria, pedras preciosas; relojoaria e instrumentos cronométricos'),
(15, 'Instrumentos musicais'),
(16, 'Papel, cartão e artigos desses materiais, não compreendidos noutras classes; produtos de imprensa; artigos de encadernação; fotografias; papelaria; adesivos (colles) para uso doméstico ou de escritório; material de arte e de desenho; pincéis; máquinas de escrever e material de escritório (exceto móveis); material de instrução ou de ensino (exceto aparelhos); matérias plásticas para embalagem (não compreendidas noutras classes); caracteres de imprensa; clichês'),
(17, 'Borracha, guta-percha, goma, amianto, mica e produtos feitos dessas matérias não compreendidos noutras classes; produtos em matérias plásticas semiprocessadas; matérias para calafetar, vedar e isolar; tubos flexíveis não metálicos'),
(18, 'Couros e peles; malas e bolsas de viagem; guarda-chuvas, guarda-sóis e bengalas; chicotes, arreios e selaria'),
(19, 'Materiais de construção não metálicos; tubos rígidos não metálicos para a construção; asfalto, piche e betume; construções transportáveis não metálicas; monumentos não metálicos'),
(20, 'Móveis, espelhos, molduras; produtos, não compreendidos noutras classes, de madeira, cortiça, junco, caniço, vime, chifre, osso, marfim, barbatana de baleia, concha, âmbar, madrepérola, espuma do mar e sucedâneos de todas estas matérias ou de matérias plásticas'),
(21, 'Utensílios e recipientes para uso doméstico ou de cozinha (não de metais preciosos nem de chapados); pentes e esponjas; escovas (exceto pincéis); materiais para fabricação de escovas; material de limpeza; palha de aço; vidro não trabalhado ou semitrabalhado (exceto vidro de construção); artigos de vidro, porcelana e faiança não compreendidos noutras classes'),
(22, 'Cordas, fios, redes, tendas, toldos, velas, sacos (não compreendidos noutras classes); matérias de enchimento (exceto de borracha ou de matérias plásticas); matérias têxteis fibrosas em bruto'),
(23, 'Fios para uso têxtil'),
(24, 'Tecidos e produtos têxteis não compreendidos noutras classes; cobertas de cama e de mesa'),
(25, 'Vestuário, calçados, chapelaria'),
(26, 'Rendas e bordados, fitas e laços; botões, colchetes e ilhoses, alfinetes e agulhas; flores artificiais'),
(27, 'Tapetes, esteiras, capachos, linóleo e outros revestimentos para pisos; tapeçarias de parede (não de matérias têxteis)'),
(28, 'Jogos, brinquedos; artigos para ginástica e desporto não compreendidos noutras classes; decorações para árvores de Natal'),
(29, 'Carne, peixe, aves e caça; extratos de carne; frutas e legumes em conserva, secos e cozidos; geléias, compotas; ovos, leite e produtos derivados do leite; óleos e gorduras comestíveis'),
(30, 'Café, chá, cacau, açúcar, arroz, tapioca, sagu, sucedâneos do café; farinhas e preparações feitas de cereais, pão, pastelaria e confeitaria, sorvetes; mel, melaço; levedura, pós para levedar; sal, mostarda; vinagre, molhos (condimentos); especiarias; gelo'),
(31, 'Produtos agrícolas, hortícolas, florestais e grãos não compreendidos noutras classes; animais vivos; frutas e legumes frescos; sementes, plantas e flores naturais; alimentos para animais'),
(32, 'Cervejas; águas minerais e gasosas e outras bebidas não alcoólicas; bebidas de frutas e sumos de frutas; xaropes e outras preparações para fazer bebidas'),
(33, 'Bebidas alcoólicas (exceto cervejas)'),
(34, 'Tabaco; artigos para fumadores; fósforos'),
(35, 'Publicidade; gestão de negócios; administração de negócios; funções de escritório'),
(36, 'Seguros; negócios financeiros; negócios monetários; negócios imobiliários'),
(37, 'Construção; reparação; serviços de instalação'),
(38, 'Telecomunicações'),
(39, 'Transporte; embalagem e armazenamento de mercadorias; organização de viagens'),
(40, 'Tratamento de materiais'),
(41, 'Educação; formação; entretenimento; atividades desportivas e culturais'),
(42, 'Serviços científicos e tecnológicos e serviços de pesquisa e concepção a eles relacionados; serviços de análise industrial e de pesquisa industrial; concepção e desenvolvimento de computadores e de software'),
(43, 'Serviços de restauração (alimentação); hospedagem temporária'),
(44, 'Serviços médicos; serviços veterinários; serviços de higiene e de beleza para seres humanos ou animais; serviços de agricultura, horticultura e silvicultura'),
(45, 'Serviços jurídicos; serviços de segurança para a proteção de bens e indivíduos; serviços pessoais e sociais prestados por terceiros para satisfazer necessidades individuais')
ON DUPLICATE KEY UPDATE descricao = VALUES(descricao);
CREATE TABLE IF NOT EXISTS relatorios_automaticos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    titulo VARCHAR(200) NOT NULL,
    arquivo VARCHAR(255) NOT NULL,
    periodo_inicial DATE NOT NULL,
    periodo_final DATE NOT NULL,
    criado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_relatorios_automaticos_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_relatorios_automaticos_usuario (usuario_id),
    INDEX idx_relatorios_automaticos_criado (criado),
    UNIQUE KEY uk_relatorio_automatico_periodo (usuario_id, periodo_inicial, periodo_final)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;