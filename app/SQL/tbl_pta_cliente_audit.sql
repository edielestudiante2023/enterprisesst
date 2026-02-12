CREATE TABLE IF NOT EXISTS tbl_pta_cliente_audit (
    id_audit          INT AUTO_INCREMENT PRIMARY KEY,
    id_ptacliente     INT,
    id_cliente        INT,
    accion            ENUM('INSERT','UPDATE','DELETE','BULK_UPDATE'),
    campo_modificado  VARCHAR(100),
    valor_anterior    TEXT,
    valor_nuevo       TEXT,
    id_usuario        INT,
    nombre_usuario    VARCHAR(255),
    email_usuario     VARCHAR(255),
    rol_usuario       VARCHAR(50),
    ip_address        VARCHAR(45),
    user_agent        TEXT,
    metodo            VARCHAR(100),
    descripcion       TEXT,
    fecha_accion      DATETIME DEFAULT CURRENT_TIMESTAMP
);
