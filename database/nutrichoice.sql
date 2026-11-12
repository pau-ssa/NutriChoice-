SET NAMES utf8mb4;
CREATE DATABASE IF NOT EXISTS `nutrichoice` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `nutrichoice`;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS lista_compras, favoritos, aportes, resenas,
    receta_sustituciones, receta_pasos, receta_ingredientes, recetas,
    producto_tiendas, producto_alergenos, productos,
    usuario_restricciones, usuarios,
    ingrediente_restricciones, ingredientes,
    restaurante_opciones, restaurante_restricciones, establecimientos,
    categorias, restricciones,
    guia_puntos, guia_secciones, guias, sustituciones;
SET FOREIGN_KEY_CHECKS = 1;

-- ---------- Catálogos base ----------
-- Características alimentarias ("Sin gluten", "Deslactosado"...).
CREATE TABLE restricciones (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  clave VARCHAR(30) NOT NULL UNIQUE,
  nombre VARCHAR(60) NOT NULL,
  descripcion VARCHAR(300) NOT NULL DEFAULT ''
) ENGINE=InnoDB;

CREATE TABLE categorias (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  tipo ENUM('producto','receta') NOT NULL,
  UNIQUE KEY uq_categoria (nombre, tipo)
) ENGINE=InnoDB;

CREATE TABLE establecimientos (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(150) NOT NULL,
  tipo ENUM('supermercado','tienda','restaurante') NOT NULL,
  provincia ENUM('San José','Alajuela','Cartago','Heredia','Guanacaste','Puntarenas','Limón') NOT NULL,
  direccion VARCHAR(250) NOT NULL,
  telefono VARCHAR(20) NOT NULL DEFAULT '',
  tipo_comida VARCHAR(60) NULL,                                   -- solo restaurantes
  seguridad ENUM('cocina_separada','opciones_adaptadas') NULL,    -- solo restaurantes
  info_nutricional TINYINT(1) NOT NULL DEFAULT 0,
  horario VARCHAR(100) NOT NULL DEFAULT '',
  descripcion VARCHAR(500) NOT NULL DEFAULT '',
  publicado TINYINT(1) NOT NULL DEFAULT 1,   -- 0 = propuesta de la comunidad pendiente de moderación
  INDEX idx_est_tipo (tipo, provincia)
) ENGINE=InnoDB;

-- Restricciones que atiende cada restaurante.
CREATE TABLE restaurante_restricciones (
  establecimiento_id INT UNSIGNED NOT NULL,
  restriccion_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (establecimiento_id, restriccion_id),
  CONSTRAINT fk_rr_est FOREIGN KEY (establecimiento_id) REFERENCES establecimientos(id) ON DELETE CASCADE,
  CONSTRAINT fk_rr_res FOREIGN KEY (restriccion_id) REFERENCES restricciones(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE restaurante_opciones (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  establecimiento_id INT UNSIGNED NOT NULL,
  orden TINYINT UNSIGNED NOT NULL DEFAULT 1,
  opcion VARCHAR(150) NOT NULL,
  CONSTRAINT fk_ro_est FOREIGN KEY (establecimiento_id) REFERENCES establecimientos(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Lista maestra de ingredientes.
CREATE TABLE ingredientes (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(120) NOT NULL UNIQUE,
  disponible_cr TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

-- Restricciones que incumple cada ingrediente.
CREATE TABLE ingrediente_restricciones (
  ingrediente_id INT UNSIGNED NOT NULL,
  restriccion_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (ingrediente_id, restriccion_id),
  CONSTRAINT fk_ir_ing FOREIGN KEY (ingrediente_id) REFERENCES ingredientes(id) ON DELETE CASCADE,
  CONSTRAINT fk_ir_res FOREIGN KEY (restriccion_id) REFERENCES restricciones(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ---------- Usuarios y perfil ----------
CREATE TABLE usuarios (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  clave_hash VARCHAR(255) NOT NULL,
  rol ENUM('usuario','colaborador','admin') NOT NULL DEFAULT 'usuario',  -- TODAVIA NO LO CONFIGURAMOS
  condicion ENUM('exclusion','diabetes','ambas','ninguna') NOT NULL DEFAULT 'ninguna',
  carb_max DECIMAL(6,1) NULL,      -- g por porción (perfil de diabetes)
  azucar_max DECIMAL(6,1) NULL,
  bloqueado TINYINT(1) NOT NULL DEFAULT 0,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE usuario_restricciones (
  usuario_id INT UNSIGNED NOT NULL,
  restriccion_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (usuario_id, restriccion_id),
  CONSTRAINT fk_ur_usu FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  CONSTRAINT fk_ur_res FOREIGN KEY (restriccion_id) REFERENCES restricciones(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ---------- Productos (valores nutricionales POR PORCIÓN) ----------
CREATE TABLE productos (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(150) NOT NULL,
  marca VARCHAR(100) NOT NULL,
  categoria_id INT UNSIGNED NOT NULL,
  precio DECIMAL(10,2) NOT NULL,
  porcion_g DECIMAL(7,1) NOT NULL,
  calorias DECIMAL(7,1) NOT NULL,
  carbohidratos DECIMAL(7,1) NOT NULL,
  azucares DECIMAL(7,1) NOT NULL,
  fibra DECIMAL(7,1) NOT NULL,
  proteina DECIMAL(7,1) NOT NULL,
  grasa DECIMAL(7,1) NOT NULL,
  sodio DECIMAL(8,1) NOT NULL,
  ingredientes TEXT NOT NULL,
  publicado TINYINT(1) NOT NULL DEFAULT 1,   -- 0 = propuesta de la comunidad pendiente de moderación
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT chk_prod_azucar CHECK (azucares <= carbohidratos),
  CONSTRAINT chk_prod_porcion CHECK (porcion_g > 0),
  CONSTRAINT fk_prod_cat FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE RESTRICT,
  INDEX idx_prod_nombre (nombre),
  INDEX idx_prod_marca (marca)
) ENGINE=InnoDB;

-- Alérgenos del producto: 'contiene' o 'trazas' (puede contener).
CREATE TABLE producto_alergenos (
  producto_id INT UNSIGNED NOT NULL,
  restriccion_id INT UNSIGNED NOT NULL,
  tipo ENUM('contiene','trazas') NOT NULL,
  PRIMARY KEY (producto_id, restriccion_id),
  CONSTRAINT fk_pa_prod FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE,
  CONSTRAINT fk_pa_res FOREIGN KEY (restriccion_id) REFERENCES restricciones(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Lugares de compra.
CREATE TABLE producto_tiendas (
  producto_id INT UNSIGNED NOT NULL,
  establecimiento_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (producto_id, establecimiento_id),
  CONSTRAINT fk_pt_prod FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE,
  CONSTRAINT fk_pt_est FOREIGN KEY (establecimiento_id) REFERENCES establecimientos(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ---------- Recetas (nutrición TOTAL; la porción se calcula) ----------
CREATE TABLE recetas (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  titulo VARCHAR(150) NOT NULL,
  categoria_id INT UNSIGNED NOT NULL,
  descripcion VARCHAR(500) NOT NULL,
  tiempo_min SMALLINT UNSIGNED NOT NULL,
  porciones TINYINT UNSIGNED NOT NULL,
  dificultad ENUM('Fácil','Media','Alta') NOT NULL DEFAULT 'Fácil',
  calorias DECIMAL(8,1) NOT NULL,
  carbohidratos DECIMAL(8,1) NOT NULL,
  azucares DECIMAL(8,1) NOT NULL,
  fibra DECIMAL(8,1) NOT NULL,
  proteina DECIMAL(8,1) NOT NULL,
  grasa DECIMAL(8,1) NOT NULL,
  usuario_id INT UNSIGNED NULL,
  publicado TINYINT(1) NOT NULL DEFAULT 1,   -- 0 = propuesta de la comunidad pendiente de moderación
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT chk_rec_porciones CHECK (porciones > 0),
  CONSTRAINT fk_rec_cat FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE RESTRICT,
  CONSTRAINT fk_rec_usu FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE receta_ingredientes (
  receta_id INT UNSIGNED NOT NULL,
  ingrediente_id INT UNSIGNED NOT NULL,
  cantidad VARCHAR(60) NOT NULL,
  orden TINYINT UNSIGNED NOT NULL DEFAULT 1,
  PRIMARY KEY (receta_id, ingrediente_id),
  CONSTRAINT fk_ri_rec FOREIGN KEY (receta_id) REFERENCES recetas(id) ON DELETE CASCADE,
  CONSTRAINT fk_ri_ing FOREIGN KEY (ingrediente_id) REFERENCES ingredientes(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE receta_pasos (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  receta_id INT UNSIGNED NOT NULL,
  orden TINYINT UNSIGNED NOT NULL,
  texto VARCHAR(600) NOT NULL,
  CONSTRAINT fk_rp_rec FOREIGN KEY (receta_id) REFERENCES recetas(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE receta_sustituciones (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  receta_id INT UNSIGNED NOT NULL,
  original VARCHAR(120) NOT NULL,
  sustituto VARCHAR(120) NOT NULL,
  motivo VARCHAR(250) NOT NULL DEFAULT '',
  CONSTRAINT fk_rs_rec FOREIGN KEY (receta_id) REFERENCES recetas(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------- Comunidad ----------
CREATE TABLE resenas (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tipo ENUM('producto','receta','restaurante') NOT NULL,
  ref_id INT UNSIGNED NOT NULL,
  usuario_id INT UNSIGNED NOT NULL,
  puntaje TINYINT UNSIGNED NOT NULL,
  comentario VARCHAR(600) NOT NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT chk_resena_puntaje CHECK (puntaje BETWEEN 1 AND 5),
  CONSTRAINT fk_resena_usu FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  INDEX idx_resena_ref (tipo, ref_id)
) ENGINE=InnoDB;

-- Aportes y reportes: pendiente → aprobado | rechazado (flujo 1 → 2 → 3).
CREATE TABLE aportes (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT UNSIGNED NOT NULL,
  tipo ENUM('producto','receta','restaurante','reporte') NOT NULL,
  entidad ENUM('producto','receta','restaurante') NULL,   -- solo reportes
  ref_id INT UNSIGNED NULL,
  corrige_id INT UNSIGNED NULL,
  titulo VARCHAR(150) NOT NULL,
  descripcion VARCHAR(1000) NOT NULL DEFAULT '',
  estado ENUM('pendiente','aprobado','rechazado') NOT NULL DEFAULT 'pendiente',
  motivo VARCHAR(300) NOT NULL DEFAULT '',
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_aporte_usu FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  INDEX idx_aporte_estado (estado)
) ENGINE=InnoDB;

CREATE TABLE favoritos (
  usuario_id INT UNSIGNED NOT NULL,
  tipo ENUM('producto','receta','restaurante') NOT NULL,
  ref_id INT UNSIGNED NOT NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (usuario_id, tipo, ref_id),
  CONSTRAINT fk_fav_usu FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE lista_compras (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT UNSIGNED NOT NULL,
  texto VARCHAR(160) NOT NULL,
  origen VARCHAR(180) NOT NULL,
  hecho TINYINT(1) NOT NULL DEFAULT 0,
  CONSTRAINT fk_compra_usu FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  UNIQUE KEY uq_compra (usuario_id, texto)
) ENGINE=InnoDB;

-- ---------- Contenido de "Primeros pasos" ----------
CREATE TABLE guias (
  clave VARCHAR(30) NOT NULL PRIMARY KEY,
  titulo VARCHAR(100) NOT NULL,
  resumen VARCHAR(300) NOT NULL,
  orden TINYINT UNSIGNED NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE guia_secciones (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  guia_clave VARCHAR(30) NOT NULL,
  orden TINYINT UNSIGNED NOT NULL,
  titulo VARCHAR(100) NOT NULL,
  CONSTRAINT fk_gs_guia FOREIGN KEY (guia_clave) REFERENCES guias(clave) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE guia_puntos (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  seccion_id INT UNSIGNED NOT NULL,
  orden TINYINT UNSIGNED NOT NULL,
  texto VARCHAR(300) NOT NULL,
  CONSTRAINT fk_gp_sec FOREIGN KEY (seccion_id) REFERENCES guia_secciones(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE sustituciones (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  grupo VARCHAR(40) NOT NULL,
  original VARCHAR(120) NOT NULL,
  sustituto VARCHAR(160) NOT NULL,
  uso VARCHAR(120) NOT NULL,
  -- Condiciones a las que aplica (incluye 'diabetes', que no es una restricción por exclusión).
  aplica_a SET('gluten','lactosa','mani','nueces','huevo','soya','mariscos','diabetes') NOT NULL
) ENGINE=InnoDB;


-- =====================================================================
-- NutriChoice |Procedimientos almacenados
-- =====================================================================
USE `nutrichoice`;
SET NAMES utf8mb4;
DELIMITER $$

-- ------------------------- RESTRICCIONES -------------------------
DROP PROCEDURE IF EXISTS sp_restriccion_consultar$$
CREATE PROCEDURE sp_restriccion_consultar(IN p_id INT)
BEGIN
    SELECT id, clave, nombre, descripcion FROM restricciones
    WHERE p_id = 0 OR id = p_id ORDER BY id;
END$$

DROP PROCEDURE IF EXISTS sp_restriccion_guardar$$
CREATE PROCEDURE sp_restriccion_guardar(IN p_id INT, IN p_clave VARCHAR(30), IN p_nombre VARCHAR(60), IN p_descripcion VARCHAR(300))
BEGIN
    IF p_id = 0 THEN
        INSERT INTO restricciones (clave, nombre, descripcion) VALUES (p_clave, p_nombre, p_descripcion);
        SET p_id = LAST_INSERT_ID();
    ELSE
        UPDATE restricciones SET clave = p_clave, nombre = p_nombre, descripcion = p_descripcion WHERE id = p_id;
    END IF;
    SELECT p_id AS id;
END$$

-- --------------------------- CATEGORÍAS --------------------------
DROP PROCEDURE IF EXISTS sp_categoria_consultar$$
CREATE PROCEDURE sp_categoria_consultar(IN p_id INT, IN p_tipo VARCHAR(20))
BEGIN
    SELECT id, nombre, tipo FROM categorias
    WHERE (p_id = 0 OR id = p_id) AND (p_tipo = '' OR tipo = p_tipo)
    ORDER BY nombre;
END$$

DROP PROCEDURE IF EXISTS sp_categoria_guardar$$
CREATE PROCEDURE sp_categoria_guardar(IN p_id INT, IN p_nombre VARCHAR(100), IN p_tipo VARCHAR(20))
BEGIN
    IF p_id = 0 THEN
        INSERT INTO categorias (nombre, tipo) VALUES (p_nombre, p_tipo);
        SET p_id = LAST_INSERT_ID();
    ELSE
        UPDATE categorias SET nombre = p_nombre, tipo = p_tipo WHERE id = p_id;
    END IF;
    SELECT p_id AS id;
END$$

-- -------------------------- INGREDIENTES -------------------------
DROP PROCEDURE IF EXISTS sp_ingrediente_consultar$$
CREATE PROCEDURE sp_ingrediente_consultar(IN p_id INT)
BEGIN
    SELECT i.id, i.nombre, i.disponible_cr,
           COALESCE((SELECT GROUP_CONCAT(r.clave ORDER BY r.id) FROM ingrediente_restricciones ir
                     JOIN restricciones r ON r.id = ir.restriccion_id WHERE ir.ingrediente_id = i.id), '') AS contiene
    FROM ingredientes i
    WHERE p_id = 0 OR i.id = p_id
    ORDER BY i.nombre;
END$$

DROP PROCEDURE IF EXISTS sp_ingrediente_guardar$$
CREATE PROCEDURE sp_ingrediente_guardar(IN p_id INT, IN p_nombre VARCHAR(120), IN p_disponible TINYINT, IN p_contiene VARCHAR(255))
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN ROLLBACK; RESIGNAL; END;
    START TRANSACTION;
    IF p_id = 0 THEN
        INSERT INTO ingredientes (nombre, disponible_cr) VALUES (p_nombre, p_disponible);
        SET p_id = LAST_INSERT_ID();
    ELSE
        UPDATE ingredientes SET nombre = p_nombre, disponible_cr = p_disponible WHERE id = p_id;
    END IF;
    DELETE FROM ingrediente_restricciones WHERE ingrediente_id = p_id;
    INSERT INTO ingrediente_restricciones (ingrediente_id, restriccion_id)
        SELECT p_id, id FROM restricciones WHERE FIND_IN_SET(clave, p_contiene);
    COMMIT;
    SELECT p_id AS id;
END$$

-- ------------------------ ESTABLECIMIENTOS -----------------------
-- p_tipo: '' todos | 'comercio' (supermercados y tiendas) | 'restaurante'
-- p_requeridas: el restaurante debe atender TODAS esas restricciones.
DROP PROCEDURE IF EXISTS sp_establecimiento_consultar$$
CREATE PROCEDURE sp_establecimiento_consultar(
    IN p_id INT, IN p_tipo VARCHAR(20), IN p_texto VARCHAR(100), IN p_provincia VARCHAR(20),
    IN p_tipo_comida VARCHAR(60), IN p_seguridad VARCHAR(30), IN p_info TINYINT, IN p_requeridas VARCHAR(255))
BEGIN
    DECLARE v_n INT DEFAULT IF(p_requeridas = '', 0,
        CHAR_LENGTH(p_requeridas) - CHAR_LENGTH(REPLACE(p_requeridas, ',', '')) + 1);
    SELECT e.*,
           COALESCE((SELECT GROUP_CONCAT(r.clave ORDER BY r.id) FROM restaurante_restricciones rr
                     JOIN restricciones r ON r.id = rr.restriccion_id WHERE rr.establecimiento_id = e.id), '') AS restricciones,
           COALESCE((SELECT GROUP_CONCAT(o.opcion ORDER BY o.orden SEPARATOR '\n')
                     FROM restaurante_opciones o WHERE o.establecimiento_id = e.id), '') AS opciones
    FROM establecimientos e
    WHERE (p_id = 0 OR e.id = p_id)
      AND (p_id > 0 OR e.publicado = 1)
      AND (p_tipo = '' OR (p_tipo = 'comercio' AND e.tipo <> 'restaurante') OR e.tipo = p_tipo)
      AND (p_texto = '' OR e.nombre LIKE CONCAT('%', p_texto, '%') OR e.tipo_comida LIKE CONCAT('%', p_texto, '%')
           OR EXISTS (SELECT 1 FROM restaurante_opciones o WHERE o.establecimiento_id = e.id AND o.opcion LIKE CONCAT('%', p_texto, '%')))
      AND (p_provincia = '' OR e.provincia = p_provincia)
      AND (p_tipo_comida = '' OR e.tipo_comida = p_tipo_comida)
      AND (p_seguridad = '' OR e.seguridad = p_seguridad)
      AND (p_info = 0 OR e.info_nutricional = 1)
      AND (v_n = 0 OR (SELECT COUNT(*) FROM restaurante_restricciones rr JOIN restricciones r ON r.id = rr.restriccion_id
                       WHERE rr.establecimiento_id = e.id AND FIND_IN_SET(r.clave, p_requeridas)) = v_n)
    ORDER BY e.nombre;
END$$

-- p_opciones: una opción apta por línea (solo restaurantes).
DROP PROCEDURE IF EXISTS sp_establecimiento_guardar$$
CREATE PROCEDURE sp_establecimiento_guardar(
    IN p_id INT, IN p_nombre VARCHAR(150), IN p_tipo VARCHAR(20), IN p_provincia VARCHAR(20), IN p_direccion VARCHAR(250),
    IN p_telefono VARCHAR(20), IN p_tipo_comida VARCHAR(60), IN p_seguridad VARCHAR(30), IN p_info TINYINT,
    IN p_horario VARCHAR(100), IN p_descripcion VARCHAR(500), IN p_restricciones VARCHAR(255), IN p_opciones TEXT,
    IN p_publicado TINYINT)
BEGIN
    DECLARE v_orden INT DEFAULT 0;
    DECLARE v_linea VARCHAR(150);
    DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN ROLLBACK; RESIGNAL; END;
    START TRANSACTION;
    IF p_id = 0 THEN
        INSERT INTO establecimientos (nombre, tipo, provincia, direccion, telefono, tipo_comida, seguridad,
                                      info_nutricional, horario, descripcion, publicado)
        VALUES (p_nombre, p_tipo, p_provincia, p_direccion, p_telefono, NULLIF(p_tipo_comida, ''),
                NULLIF(p_seguridad, ''), p_info, p_horario, p_descripcion, p_publicado);
        SET p_id = LAST_INSERT_ID();
    ELSE
        UPDATE establecimientos
        SET nombre = p_nombre, tipo = p_tipo, provincia = p_provincia, direccion = p_direccion, telefono = p_telefono,
            tipo_comida = NULLIF(p_tipo_comida, ''), seguridad = NULLIF(p_seguridad, ''), info_nutricional = p_info,
            horario = p_horario, descripcion = p_descripcion
        WHERE id = p_id;
    END IF;
    DELETE FROM restaurante_restricciones WHERE establecimiento_id = p_id;
    DELETE FROM restaurante_opciones WHERE establecimiento_id = p_id;
    IF p_tipo = 'restaurante' THEN
        INSERT INTO restaurante_restricciones (establecimiento_id, restriccion_id)
            SELECT p_id, id FROM restricciones WHERE FIND_IN_SET(clave, p_restricciones);
        WHILE p_opciones <> '' DO
            SET v_linea = TRIM(SUBSTRING_INDEX(p_opciones, '\n', 1));
            SET p_opciones = IF(LOCATE('\n', p_opciones) > 0, SUBSTRING(p_opciones, LOCATE('\n', p_opciones) + 1), '');
            IF v_linea <> '' THEN
                SET v_orden = v_orden + 1;
                INSERT INTO restaurante_opciones (establecimiento_id, orden, opcion) VALUES (p_id, v_orden, v_linea);
            END IF;
        END WHILE;
    END IF;
    COMMIT;
    SELECT p_id AS id;
END$$

-- ---------------------------- PRODUCTOS --------------------------
-- Flujo A → B: p_excluir y los rangos vienen del perfil de la persona.
DROP PROCEDURE IF EXISTS sp_producto_consultar$$
CREATE PROCEDURE sp_producto_consultar(
    IN p_id INT, IN p_texto VARCHAR(100), IN p_categoria INT, IN p_marca VARCHAR(100), IN p_tienda INT,
    IN p_precio_max DECIMAL(10,2), IN p_excluir VARCHAR(255),
    IN p_carb_max DECIMAL(7,1), IN p_azucar_max DECIMAL(7,1), IN p_orden VARCHAR(20))
BEGIN
    SELECT p.*, DATE(p.creado_en) AS creado,
           COALESCE((SELECT GROUP_CONCAT(r.clave ORDER BY r.id) FROM producto_alergenos pa JOIN restricciones r ON r.id = pa.restriccion_id
                     WHERE pa.producto_id = p.id AND pa.tipo = 'contiene'), '') AS contiene,
           COALESCE((SELECT GROUP_CONCAT(r.clave ORDER BY r.id) FROM producto_alergenos pa JOIN restricciones r ON r.id = pa.restriccion_id
                     WHERE pa.producto_id = p.id AND pa.tipo = 'trazas'), '') AS trazas,
           COALESCE((SELECT GROUP_CONCAT(pt.establecimiento_id ORDER BY pt.establecimiento_id)
                     FROM producto_tiendas pt WHERE pt.producto_id = p.id), '') AS tiendas
    FROM productos p
    WHERE (p_id = 0 OR p.id = p_id)
      AND (p_id > 0 OR p.publicado = 1)
      AND (p_texto = '' OR p.nombre LIKE CONCAT('%', p_texto, '%') OR p.marca LIKE CONCAT('%', p_texto, '%')
           OR p.ingredientes LIKE CONCAT('%', p_texto, '%'))
      AND (p_categoria = 0 OR p.categoria_id = p_categoria)
      AND (p_marca = '' OR p.marca = p_marca)
      AND (p_tienda = 0 OR EXISTS (SELECT 1 FROM producto_tiendas pt WHERE pt.producto_id = p.id AND pt.establecimiento_id = p_tienda))
      AND (p_precio_max IS NULL OR p.precio <= p_precio_max)
      AND (p_carb_max IS NULL OR p.carbohidratos <= p_carb_max)
      AND (p_azucar_max IS NULL OR p.azucares <= p_azucar_max)
      AND NOT EXISTS (SELECT 1 FROM producto_alergenos pa JOIN restricciones r ON r.id = pa.restriccion_id
                      WHERE pa.producto_id = p.id AND pa.tipo = 'contiene' AND FIND_IN_SET(r.clave, p_excluir))
    ORDER BY
      CASE WHEN p_orden = 'precio' THEN p.precio END ASC,
      CASE WHEN p_orden = 'carbohidratos' THEN p.carbohidratos END ASC,
      CASE WHEN p_orden = 'recientes' THEN p.creado_en END DESC,
      p.nombre ASC;
END$$

DROP PROCEDURE IF EXISTS sp_producto_guardar$$
CREATE PROCEDURE sp_producto_guardar(
    IN p_id INT, IN p_nombre VARCHAR(150), IN p_marca VARCHAR(100), IN p_categoria INT, IN p_precio DECIMAL(10,2),
    IN p_porcion DECIMAL(7,1), IN p_calorias DECIMAL(7,1), IN p_carbohidratos DECIMAL(7,1), IN p_azucares DECIMAL(7,1),
    IN p_fibra DECIMAL(7,1), IN p_proteina DECIMAL(7,1), IN p_grasa DECIMAL(7,1), IN p_sodio DECIMAL(8,1),
    IN p_ingredientes TEXT, IN p_contiene VARCHAR(255), IN p_trazas VARCHAR(255), IN p_tiendas VARCHAR(255),
    IN p_publicado TINYINT)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN ROLLBACK; RESIGNAL; END;
    START TRANSACTION;
    IF p_id = 0 THEN
        INSERT INTO productos (nombre, marca, categoria_id, precio, porcion_g, calorias, carbohidratos, azucares,
                               fibra, proteina, grasa, sodio, ingredientes, publicado)
        VALUES (p_nombre, p_marca, p_categoria, p_precio, p_porcion, p_calorias, p_carbohidratos, p_azucares,
                p_fibra, p_proteina, p_grasa, p_sodio, p_ingredientes, p_publicado);
        SET p_id = LAST_INSERT_ID();
    ELSE
        UPDATE productos
        SET nombre = p_nombre, marca = p_marca, categoria_id = p_categoria, precio = p_precio, porcion_g = p_porcion,
            calorias = p_calorias, carbohidratos = p_carbohidratos, azucares = p_azucares, fibra = p_fibra,
            proteina = p_proteina, grasa = p_grasa, sodio = p_sodio, ingredientes = p_ingredientes
        WHERE id = p_id;
    END IF;
    DELETE FROM producto_alergenos WHERE producto_id = p_id;
    DELETE FROM producto_tiendas WHERE producto_id = p_id;
    INSERT INTO producto_alergenos (producto_id, restriccion_id, tipo)
        SELECT p_id, id, 'contiene' FROM restricciones WHERE FIND_IN_SET(clave, p_contiene);
    INSERT IGNORE INTO producto_alergenos (producto_id, restriccion_id, tipo)
        SELECT p_id, id, 'trazas' FROM restricciones WHERE FIND_IN_SET(clave, p_trazas);
    INSERT INTO producto_tiendas (producto_id, establecimiento_id)
        SELECT p_id, id FROM establecimientos WHERE tipo <> 'restaurante' AND FIND_IN_SET(id, p_tiendas);
    COMMIT;
    SELECT p_id AS id;
END$$

-- ----------------------------- RECETAS ---------------------------
-- MySQL calcula la nutrición por porción y los alérgenos desde los ingredientes.
-- p_id = 0: un resultado (lista de recetas filtrada).
-- p_id > 0: cuatro resultados seguidos → 1) la receta, 2) ingredientes, 3) pasos, 4) sustituciones.
DROP PROCEDURE IF EXISTS sp_receta_consultar$$
CREATE PROCEDURE sp_receta_consultar(
    IN p_id INT, IN p_texto VARCHAR(100), IN p_categoria INT, IN p_tiempo_max INT, IN p_excluir VARCHAR(255),
    IN p_carb_max DECIMAL(7,1), IN p_azucar_max DECIMAL(7,1))
BEGIN
    SELECT r.*, DATE(r.creado_en) AS creado,
           ROUND(r.calorias / r.porciones, 1) AS porcion_calorias,
           ROUND(r.carbohidratos / r.porciones, 1) AS porcion_carbohidratos,
           ROUND(r.azucares / r.porciones, 1) AS porcion_azucares,
           ROUND(r.fibra / r.porciones, 1) AS porcion_fibra,
           ROUND(r.proteina / r.porciones, 1) AS porcion_proteina,
           ROUND(r.grasa / r.porciones, 1) AS porcion_grasa,
           COALESCE((SELECT GROUP_CONCAT(DISTINCT rs.clave ORDER BY rs.id) FROM receta_ingredientes ri
                     JOIN ingrediente_restricciones ir ON ir.ingrediente_id = ri.ingrediente_id
                     JOIN restricciones rs ON rs.id = ir.restriccion_id WHERE ri.receta_id = r.id), '') AS contiene
    FROM recetas r
    WHERE (p_id = 0 OR r.id = p_id)
      AND (p_id > 0 OR r.publicado = 1)
      AND (p_texto = '' OR r.titulo LIKE CONCAT('%', p_texto, '%') OR r.descripcion LIKE CONCAT('%', p_texto, '%')
           OR EXISTS (SELECT 1 FROM receta_ingredientes ri JOIN ingredientes i ON i.id = ri.ingrediente_id
                      WHERE ri.receta_id = r.id AND i.nombre LIKE CONCAT('%', p_texto, '%')))
      AND (p_categoria = 0 OR r.categoria_id = p_categoria)
      AND (p_tiempo_max = 0 OR r.tiempo_min <= p_tiempo_max)
      AND (p_carb_max IS NULL OR r.carbohidratos / r.porciones <= p_carb_max)
      AND (p_azucar_max IS NULL OR r.azucares / r.porciones <= p_azucar_max)
      AND NOT EXISTS (SELECT 1 FROM receta_ingredientes ri
                      JOIN ingrediente_restricciones ir ON ir.ingrediente_id = ri.ingrediente_id
                      JOIN restricciones rs ON rs.id = ir.restriccion_id
                      WHERE ri.receta_id = r.id AND FIND_IN_SET(rs.clave, p_excluir))
    ORDER BY r.creado_en DESC, r.id DESC;

    IF p_id > 0 THEN
        -- 2) Ingredientes con las restricciones que incumple cada uno.
        SELECT ri.ingrediente_id, i.nombre, ri.cantidad,
               COALESCE((SELECT GROUP_CONCAT(rs.clave ORDER BY rs.id) FROM ingrediente_restricciones ir
                         JOIN restricciones rs ON rs.id = ir.restriccion_id WHERE ir.ingrediente_id = i.id), '') AS contiene
        FROM receta_ingredientes ri JOIN ingredientes i ON i.id = ri.ingrediente_id
        WHERE ri.receta_id = p_id ORDER BY ri.orden;
        -- 3) Pasos.
        SELECT texto FROM receta_pasos WHERE receta_id = p_id ORDER BY orden;
        -- 4) Sustituciones.
        SELECT original, sustituto, motivo FROM receta_sustituciones WHERE receta_id = p_id ORDER BY id;
    END IF;
END$$

-- Listas en texto, un elemento por línea; los campos de cada línea van separados por tab :
--   p_ingredientes:   "id_ingrediente<TAB>cantidad"
--   p_pasos:          "texto del paso"
--   p_sustituciones:  "original<TAB>sustituto<TAB>motivo"
DROP PROCEDURE IF EXISTS sp_receta_guardar$$
CREATE PROCEDURE sp_receta_guardar(
    IN p_id INT, IN p_titulo VARCHAR(150), IN p_categoria INT, IN p_descripcion VARCHAR(500), IN p_tiempo INT,
    IN p_porciones INT, IN p_dificultad VARCHAR(10), IN p_calorias DECIMAL(8,1), IN p_carbohidratos DECIMAL(8,1),
    IN p_azucares DECIMAL(8,1), IN p_fibra DECIMAL(8,1), IN p_proteina DECIMAL(8,1), IN p_grasa DECIMAL(8,1),
    IN p_usuario INT, IN p_ingredientes TEXT, IN p_pasos TEXT, IN p_sustituciones TEXT, IN p_publicado TINYINT)
BEGIN
    DECLARE v_linea VARCHAR(700);
    DECLARE v_orden INT DEFAULT 0;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN ROLLBACK; RESIGNAL; END;
    START TRANSACTION;
    IF p_id = 0 THEN
        INSERT INTO recetas (titulo, categoria_id, descripcion, tiempo_min, porciones, dificultad, calorias,
                             carbohidratos, azucares, fibra, proteina, grasa, usuario_id, publicado)
        VALUES (p_titulo, p_categoria, p_descripcion, p_tiempo, p_porciones, p_dificultad, p_calorias,
                p_carbohidratos, p_azucares, p_fibra, p_proteina, p_grasa, NULLIF(p_usuario, 0), p_publicado);
        SET p_id = LAST_INSERT_ID();
    ELSE
        UPDATE recetas
        SET titulo = p_titulo, categoria_id = p_categoria, descripcion = p_descripcion, tiempo_min = p_tiempo,
            porciones = p_porciones, dificultad = p_dificultad, calorias = p_calorias, carbohidratos = p_carbohidratos,
            azucares = p_azucares, fibra = p_fibra, proteina = p_proteina, grasa = p_grasa
        WHERE id = p_id;
    END IF;
    DELETE FROM receta_ingredientes WHERE receta_id = p_id;
    DELETE FROM receta_pasos WHERE receta_id = p_id;
    DELETE FROM receta_sustituciones WHERE receta_id = p_id;

    -- Ingredientes
    SET v_orden = 0;
    WHILE p_ingredientes <> '' DO
        SET v_linea = SUBSTRING_INDEX(p_ingredientes, '\n', 1);
        SET p_ingredientes = IF(LOCATE('\n', p_ingredientes) > 0, SUBSTRING(p_ingredientes, LOCATE('\n', p_ingredientes) + 1), '');
        IF v_linea <> '' THEN
            SET v_orden = v_orden + 1;
            INSERT INTO receta_ingredientes (receta_id, ingrediente_id, cantidad, orden)
            VALUES (p_id, SUBSTRING_INDEX(v_linea, '\t', 1), SUBSTRING_INDEX(v_linea, '\t', -1), v_orden)
            ON DUPLICATE KEY UPDATE cantidad = VALUES(cantidad);
        END IF;
    END WHILE;

    -- Pasos
    SET v_orden = 0;
    WHILE p_pasos <> '' DO
        SET v_linea = SUBSTRING_INDEX(p_pasos, '\n', 1);
        SET p_pasos = IF(LOCATE('\n', p_pasos) > 0, SUBSTRING(p_pasos, LOCATE('\n', p_pasos) + 1), '');
        IF v_linea <> '' THEN
            SET v_orden = v_orden + 1;
            INSERT INTO receta_pasos (receta_id, orden, texto) VALUES (p_id, v_orden, v_linea);
        END IF;
    END WHILE;

    -- Sustituciones
    WHILE p_sustituciones <> '' DO
        SET v_linea = SUBSTRING_INDEX(p_sustituciones, '\n', 1);
        SET p_sustituciones = IF(LOCATE('\n', p_sustituciones) > 0, SUBSTRING(p_sustituciones, LOCATE('\n', p_sustituciones) + 1), '');
        IF v_linea <> '' THEN
            INSERT INTO receta_sustituciones (receta_id, original, sustituto, motivo)
            VALUES (p_id, SUBSTRING_INDEX(v_linea, '\t', 1),
                    SUBSTRING_INDEX(SUBSTRING_INDEX(v_linea, '\t', 2), '\t', -1),
                    SUBSTRING_INDEX(v_linea, '\t', -1));
        END IF;
    END WHILE;
    COMMIT;
    SELECT p_id AS id;
END$$

-- ----------------------------- USUARIOS --------------------------
-- p_id > 0 por id; p_email <> '' por correo (incluye clave_hash para autenticar); ambos vacíos: todos.
DROP PROCEDURE IF EXISTS sp_usuario_consultar$$
CREATE PROCEDURE sp_usuario_consultar(IN p_id INT, IN p_email VARCHAR(190))
BEGIN
    SELECT u.id, u.nombre, u.email, IF(p_email <> '', u.clave_hash, NULL) AS clave_hash, u.rol,
           u.condicion, u.carb_max, u.azucar_max, u.bloqueado, DATE(u.creado_en) AS creado,
           COALESCE((SELECT GROUP_CONCAT(r.clave ORDER BY r.id) FROM usuario_restricciones ur
                     JOIN restricciones r ON r.id = ur.restriccion_id WHERE ur.usuario_id = u.id), '') AS restricciones,
           COALESCE((SELECT GROUP_CONCAT(CONCAT(f.tipo, ':', f.ref_id) ORDER BY f.creado_en DESC)
                     FROM favoritos f WHERE f.usuario_id = u.id), '') AS favoritos
    FROM usuarios u
    WHERE (p_id = 0 OR u.id = p_id) AND (p_email = '' OR u.email = p_email)
    ORDER BY u.nombre;
END$$

-- p_hash NULL conserva la contraseña actual (al actualizar el perfil).
DROP PROCEDURE IF EXISTS sp_usuario_guardar$$
CREATE PROCEDURE sp_usuario_guardar(
    IN p_id INT, IN p_nombre VARCHAR(120), IN p_email VARCHAR(190), IN p_hash VARCHAR(255), IN p_rol VARCHAR(20), IN p_condicion VARCHAR(20),
    IN p_carb DECIMAL(6,1), IN p_azucar DECIMAL(6,1), IN p_bloqueado TINYINT, IN p_restricciones VARCHAR(255))
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN ROLLBACK; RESIGNAL; END;
    START TRANSACTION;
    IF p_id = 0 THEN
        INSERT INTO usuarios (nombre, email, clave_hash, rol, condicion, carb_max, azucar_max, bloqueado)
        VALUES (p_nombre, p_email, p_hash, p_rol, p_condicion, p_carb, p_azucar, p_bloqueado);
        SET p_id = LAST_INSERT_ID();
    ELSE
        UPDATE usuarios
        SET nombre = p_nombre, email = p_email, clave_hash = COALESCE(p_hash, clave_hash), rol = p_rol, condicion = p_condicion,
            carb_max = p_carb, azucar_max = p_azucar, bloqueado = p_bloqueado
        WHERE id = p_id;
    END IF;
    DELETE FROM usuario_restricciones WHERE usuario_id = p_id;
    INSERT INTO usuario_restricciones (usuario_id, restriccion_id)
        SELECT p_id, id FROM restricciones WHERE FIND_IN_SET(clave, p_restricciones);
    COMMIT;
    SELECT p_id AS id;
END$$

-- Agrega el favorito si no existe o lo quita si existe.
DROP PROCEDURE IF EXISTS sp_favorito_alternar$$
CREATE PROCEDURE sp_favorito_alternar(IN p_usuario INT, IN p_tipo VARCHAR(20), IN p_ref INT)
BEGIN
    IF EXISTS (SELECT 1 FROM favoritos WHERE usuario_id = p_usuario AND tipo = p_tipo AND ref_id = p_ref) THEN
        DELETE FROM favoritos WHERE usuario_id = p_usuario AND tipo = p_tipo AND ref_id = p_ref;
        SELECT 0 AS agregado;
    ELSE
        INSERT INTO favoritos (usuario_id, tipo, ref_id) VALUES (p_usuario, p_tipo, p_ref);
        SELECT 1 AS agregado;
    END IF;
END$$

-- p_accion: listar | agregar | marcar | quitar | vaciar | vaciar_hechos
DROP PROCEDURE IF EXISTS sp_compra_gestionar$$
CREATE PROCEDURE sp_compra_gestionar(IN p_accion VARCHAR(20), IN p_usuario INT, IN p_id INT,
                                     IN p_texto VARCHAR(160), IN p_origen VARCHAR(180))
BEGIN
    CASE p_accion
        WHEN 'agregar' THEN INSERT IGNORE INTO lista_compras (usuario_id, texto, origen) VALUES (p_usuario, p_texto, p_origen);
        WHEN 'marcar' THEN UPDATE lista_compras SET hecho = 1 - hecho WHERE id = p_id AND usuario_id = p_usuario;
        WHEN 'quitar' THEN DELETE FROM lista_compras WHERE id = p_id AND usuario_id = p_usuario;
        WHEN 'vaciar' THEN DELETE FROM lista_compras WHERE usuario_id = p_usuario;
        WHEN 'vaciar_hechos' THEN DELETE FROM lista_compras WHERE usuario_id = p_usuario AND hecho = 1;
        ELSE BEGIN END;
    END CASE;
    IF p_accion = 'listar' THEN
        SELECT id, texto, origen, hecho FROM lista_compras WHERE usuario_id = p_usuario ORDER BY hecho, id;
    ELSE
        SELECT ROW_COUNT() AS filas;
    END IF;
END$$

-- ----------------------------- RESEÑAS ---------------------------
DROP PROCEDURE IF EXISTS sp_resena_consultar$$
CREATE PROCEDURE sp_resena_consultar(IN p_tipo VARCHAR(20), IN p_ref INT)
BEGIN
    -- Autor abreviado para proteger la identidad: "Ana C."
    SELECT re.id, re.puntaje, re.comentario, DATE(re.creado_en) AS fecha,
           CONCAT(SUBSTRING_INDEX(u.nombre, ' ', 1),
                  IF(LOCATE(' ', u.nombre) > 0, CONCAT(' ', LEFT(SUBSTRING_INDEX(u.nombre, ' ', -1), 1), '.'), '')) AS autor
    FROM resenas re JOIN usuarios u ON u.id = re.usuario_id
    WHERE re.tipo = p_tipo AND re.ref_id = p_ref
    ORDER BY re.creado_en DESC, re.id DESC;
END$$

DROP PROCEDURE IF EXISTS sp_resena_guardar$$
CREATE PROCEDURE sp_resena_guardar(IN p_tipo VARCHAR(20), IN p_ref INT, IN p_usuario INT, IN p_puntaje INT, IN p_comentario VARCHAR(600))
BEGIN
    INSERT INTO resenas (tipo, ref_id, usuario_id, puntaje, comentario) VALUES (p_tipo, p_ref, p_usuario, p_puntaje, p_comentario);
    SELECT LAST_INSERT_ID() AS id;
END$$

-- ----------------------------- APORTES ---------------------------
-- Flujo 1 → 2 → 3. p_estado = '' todos; p_usuario = 0 de todas las personas.
DROP PROCEDURE IF EXISTS sp_aporte_consultar$$
CREATE PROCEDURE sp_aporte_consultar(IN p_id INT, IN p_estado VARCHAR(20), IN p_usuario INT)
BEGIN
    SELECT a.id, a.usuario_id, u.nombre AS autor, a.tipo, a.entidad, a.ref_id, a.corrige_id, a.titulo,
           a.descripcion, a.estado, a.motivo, DATE(a.creado_en) AS fecha
    FROM aportes a JOIN usuarios u ON u.id = a.usuario_id
    WHERE (p_id = 0 OR a.id = p_id) AND (p_estado = '' OR a.estado = p_estado) AND (p_usuario = 0 OR a.usuario_id = p_usuario)
    ORDER BY a.creado_en DESC, a.id DESC;
END$$

-- p_id = 0 registra un aporte pendiente.
-- p_id > 0 lo resuelve (solo si sigue pendiente). Al aprobar una propuesta NUEVA se publica
-- el registro (publicado = 1). Las correcciones las aplica PHP con sp_<entidad>_guardar.
DROP PROCEDURE IF EXISTS sp_aporte_guardar$$
CREATE PROCEDURE sp_aporte_guardar(
    IN p_id INT, IN p_usuario INT, IN p_tipo VARCHAR(20), IN p_entidad VARCHAR(20), IN p_ref INT, IN p_corrige INT,
    IN p_titulo VARCHAR(150), IN p_descripcion VARCHAR(1000), IN p_estado VARCHAR(20), IN p_motivo VARCHAR(300))
BEGIN
    DECLARE v_tipo VARCHAR(20);
    DECLARE v_ref INT;
    DECLARE v_corrige INT;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN ROLLBACK; RESIGNAL; END;
    START TRANSACTION;
    IF p_id = 0 THEN
        INSERT INTO aportes (usuario_id, tipo, entidad, ref_id, corrige_id, titulo, descripcion)
        VALUES (p_usuario, p_tipo, NULLIF(p_entidad, ''), NULLIF(p_ref, 0), NULLIF(p_corrige, 0), p_titulo, p_descripcion);
        SET p_id = LAST_INSERT_ID();
    ELSE
        SELECT tipo, ref_id, corrige_id INTO v_tipo, v_ref, v_corrige FROM aportes WHERE id = p_id AND estado = 'pendiente';
        UPDATE aportes SET estado = p_estado, motivo = p_motivo WHERE id = p_id AND estado = 'pendiente';
        IF p_estado = 'aprobado' AND v_corrige IS NULL THEN
            CASE v_tipo
                WHEN 'producto' THEN UPDATE productos SET publicado = 1 WHERE id = v_ref;
                WHEN 'receta' THEN UPDATE recetas SET publicado = 1 WHERE id = v_ref;
                WHEN 'restaurante' THEN UPDATE establecimientos SET publicado = 1 WHERE id = v_ref;
                ELSE BEGIN END;
            END CASE;
        END IF;
    END IF;
    COMMIT;
    SELECT p_id AS id;
END$$

-- -------------------------- PRIMEROS PASOS -----------------------
DROP PROCEDURE IF EXISTS sp_guia_consultar$$
CREATE PROCEDURE sp_guia_consultar()
BEGIN
    SELECT g.clave, g.titulo AS guia_titulo, g.resumen, s.id AS seccion_id, s.titulo AS seccion_titulo, p.texto
    FROM guias g
    JOIN guia_secciones s ON s.guia_clave = g.clave
    JOIN guia_puntos p ON p.seccion_id = s.id
    ORDER BY g.orden, s.orden, p.orden;
END$$

DROP PROCEDURE IF EXISTS sp_sustitucion_consultar$$
CREATE PROCEDURE sp_sustitucion_consultar(IN p_grupo VARCHAR(40))
BEGIN
    SELECT id, grupo, original, sustituto, uso, aplica_a FROM sustituciones
    WHERE p_grupo = '' OR grupo = p_grupo ORDER BY id;
END$$

-- ---------------------- BORRADO DE CATÁLOGOS ---------------------
-- Las llaves foráneas ON DELETE RESTRICT impiden borrar registros en uso (error 23000).
DROP PROCEDURE IF EXISTS sp_eliminar$$
CREATE PROCEDURE sp_eliminar(IN p_tabla VARCHAR(30), IN p_id INT)
BEGIN
    CASE p_tabla
        WHEN 'productos' THEN DELETE FROM productos WHERE id = p_id;
        WHEN 'recetas' THEN DELETE FROM recetas WHERE id = p_id;
        WHEN 'ingredientes' THEN DELETE FROM ingredientes WHERE id = p_id;
        WHEN 'establecimientos' THEN DELETE FROM establecimientos WHERE id = p_id;
        WHEN 'categorias' THEN DELETE FROM categorias WHERE id = p_id;
        WHEN 'restricciones' THEN DELETE FROM restricciones WHERE id = p_id;
        ELSE SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Tabla no permitida.';
    END CASE;
    SELECT ROW_COUNT() AS filas;
END$$

DELIMITER ;
