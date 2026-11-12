USE `nutrichoice`;
SET NAMES utf8mb4;

-- Características alimentarias
INSERT INTO restricciones (id, clave, nombre, descripcion) VALUES
  (1, 'gluten', 'Sin gluten', 'Enfermedad celíaca o sensibilidad al gluten (trigo, cebada, centeno).'),
  (2, 'lactosa', 'Sin lactosa', 'Intolerancia a la lactosa o alergia a la proteína de la leche.'),
  (3, 'mani', 'Sin maní', 'Alergia al maní (cacahuate).'),
  (4, 'nueces', 'Sin nueces', 'Alergia a frutos de cáscara: almendra, marañón, nuez, avellana.'),
  (5, 'huevo', 'Sin huevo', 'Alergia al huevo.'),
  (6, 'soya', 'Sin soya', 'Alergia a la soya.'),
  (7, 'mariscos', 'Sin mariscos', 'Alergia a crustáceos y moluscos.');

-- Categorías
INSERT INTO categorias (id, nombre, tipo) VALUES
  (1, 'Panadería y harinas', 'producto'),
  (2, 'Lácteos y alternativas', 'producto'),
  (3, 'Snacks', 'producto'),
  (4, 'Granos y pastas', 'producto'),
  (5, 'Bebidas', 'producto'),
  (6, 'Endulzantes', 'producto'),
  (7, 'Desayuno', 'receta'),
  (8, 'Plato fuerte', 'receta'),
  (9, 'Repostería', 'receta'),
  (10, 'Merienda', 'receta');

-- Supermercados, tiendas y restaurantes
INSERT INTO establecimientos (id, nombre, tipo, provincia, direccion, telefono, tipo_comida, seguridad, info_nutricional, horario, descripcion) VALUES
  (1, 'MasxMenos', 'supermercado', 'San José', 'Curridabat, 200 m este de la rotonda', '2222-1001', NULL, NULL, 0, '', ''),
  (2, 'MegaSuper', 'supermercado', 'Heredia', 'Heredia centro, frente al parque', '2260-2002', NULL, NULL, 0, '', ''),
  (3, 'Naturista Vida Sana', 'tienda', 'San José', 'Barrio Escalante, calle 33', '2280-3003', NULL, NULL, 0, '', ''),
  (4, 'Mercado del Valle', 'supermercado', 'Cartago', 'Cartago, contiguo a las ruinas', '2551-4004', NULL, NULL, 0, '', ''),
  (5, 'Club Mayoreo', 'supermercado', 'Alajuela', 'Alajuela, autopista General Cañas', '2440-5005', NULL, NULL, 0, '', ''),
  (101, 'Soda La Libre', 'restaurante', 'San José', 'San Pedro, 100 m norte de la iglesia', '2225-1101', 'Comida típica', 'cocina_separada', 1, 'L-S 7:00 a 17:00', 'Soda con cocina 100 % libre de gluten y menú adaptado para diabetes.'),
  (102, 'Verde Bistró', 'restaurante', 'Heredia', 'Santo Domingo, plaza Las Flores, local 4', '2244-1102', 'Vegetariana', 'opciones_adaptadas', 1, 'M-D 11:00 a 21:00', 'Opciones veganas marcadas en el menú; la cocina es compartida.'),
  (103, 'Pizzería Masa Madre', 'restaurante', 'San José', 'Escazú, Avenida Escazú, local 12', '2288-1103', 'Italiana', 'cocina_separada', 0, 'M-D 12:00 a 22:00', 'Horno exclusivo para masas sin gluten.'),
  (104, 'Mar y Tierra Puntarenas', 'restaurante', 'Puntarenas', 'Paseo de los Turistas, 300 m oeste del muelle', '2661-1104', 'Mariscos', 'opciones_adaptadas', 0, 'M-D 10:00 a 20:00', 'Personal capacitado para pedidos sin gluten; consultar contacto cruzado.'),
  (105, 'Café Equilibrio', 'restaurante', 'Cartago', 'Paraíso, frente a la plaza', '2575-1105', 'Cafetería', 'opciones_adaptadas', 1, 'L-S 6:30 a 18:00', 'Carta con conteo de carbohidratos por porción.');

INSERT INTO restaurante_restricciones (establecimiento_id, restriccion_id) VALUES
  (101, 1),
  (101, 2),
  (101, 3),
  (102, 2),
  (102, 5),
  (102, 7),
  (103, 1),
  (104, 1),
  (104, 2),
  (105, 1),
  (105, 3),
  (105, 4);

INSERT INTO restaurante_opciones (establecimiento_id, orden, opcion) VALUES
  (101, 1, 'Casado con tortilla de maíz'),
  (101, 2, 'Gallo pinto sin natilla'),
  (101, 3, 'Picadillo de arracache'),
  (102, 1, 'Bowl de quinoa'),
  (102, 2, 'Tacos de hongos'),
  (102, 3, 'Postre de chía sin azúcar'),
  (103, 1, 'Pizza con masa de yuca'),
  (103, 2, 'Pasta de arroz al pomodoro'),
  (104, 1, 'Pescado a la plancha con ensalada'),
  (104, 2, 'Ceviche sin galletas'),
  (105, 1, 'Queque de zanahoria sin azúcar'),
  (105, 2, 'Pan de yuca'),
  (105, 3, 'Café con bebida de avena');

-- Ingredientes
INSERT INTO ingredientes (id, nombre, disponible_cr) VALUES
  (1, 'Harina de arroz', 1),
  (2, 'Harina de trigo', 1),
  (3, 'Fécula de yuca', 1),
  (4, 'Masa de maíz', 1),
  (5, 'Leche de vaca', 1),
  (6, 'Bebida de avena sin gluten', 1),
  (7, 'Huevo', 1),
  (8, 'Queso turrialba', 1),
  (9, 'Frijoles negros', 1),
  (10, 'Arroz', 1),
  (11, 'Plátano maduro', 1),
  (12, 'Zanahoria', 1),
  (13, 'Eritritol', 1),
  (14, 'Azúcar', 1),
  (15, 'Mantequilla de maní', 1),
  (16, 'Aceite de coco', 1),
  (17, 'Chayote', 1),
  (18, 'Pollo', 1),
  (19, 'Culantro', 1),
  (20, 'Semillas de chía', 1),
  (21, 'Salsa de soya', 1),
  (22, 'Almendras', 1),
  (23, 'Canela', 1),
  (24, 'Cebolla', 1);

INSERT INTO ingrediente_restricciones (ingrediente_id, restriccion_id) VALUES
  (2, 1),
  (5, 2),
  (7, 5),
  (8, 2),
  (15, 3),
  (21, 6),
  (21, 1),
  (22, 4);

-- Usuarios de laboratorio (contraseñas en el README; solo localhost)
INSERT INTO usuarios (id, nombre, email, clave_hash, rol, condicion, carb_max, azucar_max, bloqueado, creado_en) VALUES
  (1, 'Pepe Administrador', 'admin@nutrichoice.com', '$2y$10$ehtuETqcgpdlTSRa3oynTu3NnW6OEwP7VJQ09lwed7QZ0M0RZYbZa', 'admin', 'ninguna', NULL, NULL, 0, '2026-10-01 08:00:00'),
  (2, 'Ana Colaboradora', 'colaborador@nutrichoice.com', '$2y$10$iAIYz53F83NPcGJQ7mh4z.axZnPsKtQZLc27yA.S6ux8Yo3LdhUIC', 'colaborador', 'exclusion', NULL, NULL, 0, '2026-10-03 08:00:00'),
  (3, 'Carlos Usuario', 'carlosusuario@gmail.com', '$2y$10$jbmzc2CqsnVxwTrcC2uB/O4.2eUwxS0odubmaG3hUgF941MEHPRmW', 'usuario', 'ambas', 30, 6, 0, '2026-10-10 08:00:00');

INSERT INTO usuario_restricciones (usuario_id, restriccion_id) VALUES
  (2, 1),
  (3, 2);

-- Productos (valores por porción)
INSERT INTO productos (id, nombre, marca, categoria_id, precio, porcion_g, calorias, carbohidratos, azucares, fibra, proteina, grasa, sodio, ingredientes, creado_en) VALUES
  (1, 'Pan de molde sin gluten', 'Molino Libre', 1, 3850, 50, 120, 22, 2.5, 3, 2, 3, 190, 'Harina de arroz, fécula de yuca, agua, aceite vegetal, huevo, levadura, sal.', '2026-10-28 08:00:00'),
  (2, 'Bebida de avena sin azúcar', 'Finca Celeste', 2, 2450, 240, 90, 14, 3, 2, 2, 3, 100, 'Agua, avena certificada sin gluten, aceite de girasol, carbonato de calcio, sal.', '2026-10-30 08:00:00'),
  (3, 'Tortillas de maíz amarillo', 'Doña Tica', 1, 1200, 60, 130, 27, 0.5, 3, 3, 1.5, 20, 'Maíz nixtamalizado, agua, cal.', '2026-10-15 08:00:00'),
  (4, 'Galletas de chocolate con maní', 'Crujitos', 3, 1650, 30, 150, 19, 9, 1, 3, 7, 95, 'Harina de trigo, azúcar, maní, cacao, aceite de palma, lecitina de soya.', '2026-09-20 08:00:00'),
  (5, 'Yogur griego natural deslactosado', 'Lácteos del Valle', 2, 1900, 150, 110, 6, 5, 0, 15, 3, 60, 'Leche descremada deslactosada, cultivos lácticos, enzima lactasa.', '2026-11-02 08:00:00'),
  (6, 'Pasta de arroz integral', 'Tierra Verde', 4, 2700, 56, 200, 43, 0, 2, 4, 1.5, 0, 'Arroz integral, agua.', '2026-10-05 08:00:00'),
  (7, 'Eritritol granulado', 'DulceZero', 6, 4200, 5, 0, 5, 0, 0, 0, 0, 0, 'Eritritol.', '2026-11-06 08:00:00'),
  (8, 'Harina multiuso sin gluten', 'Molino Libre', 1, 3300, 30, 105, 24, 0, 1, 1.5, 0.5, 5, 'Harina de arroz, fécula de papa, fécula de yuca, goma xantana.', '2026-11-08 08:00:00'),
  (9, 'Refresco de cas sin azúcar', 'Frutal CR', 5, 950, 250, 15, 4, 3, 0, 0, 0, 15, 'Agua, pulpa de cas, ácido cítrico, sucralosa.', '2026-10-20 08:00:00'),
  (10, 'Mezcla de nueces y semillas', 'Tierra Verde', 3, 3950, 30, 180, 6, 1.5, 3, 6, 15, 0, 'Almendras, marañón, semillas de marañón, semillas de girasol, semillas de calabaza.', '2026-11-09 08:00:00');

INSERT INTO producto_alergenos (producto_id, restriccion_id, tipo) VALUES
  (1, 5, 'contiene'),
  (1, 6, 'trazas'),
  (4, 1, 'contiene'),
  (4, 3, 'contiene'),
  (4, 6, 'contiene'),
  (4, 4, 'trazas'),
  (4, 2, 'trazas'),
  (10, 4, 'contiene'),
  (10, 3, 'trazas');

INSERT INTO producto_tiendas (producto_id, establecimiento_id) VALUES
  (1, 1),
  (1, 3),
  (2, 1),
  (2, 2),
  (2, 4),
  (3, 1),
  (3, 2),
  (3, 4),
  (3, 5),
  (4, 2),
  (4, 5),
  (5, 1),
  (5, 2),
  (5, 4),
  (6, 1),
  (6, 3),
  (7, 3),
  (7, 1),
  (8, 1),
  (8, 3),
  (8, 5),
  (9, 2),
  (9, 4),
  (9, 5),
  (10, 3),
  (10, 5);

-- Recetas (nutrición total de la receta)
INSERT INTO recetas (id, titulo, categoria_id, descripcion, tiempo_min, porciones, dificultad, calorias, carbohidratos, azucares, fibra, proteina, grasa, usuario_id, creado_en) VALUES
  (1, 'Gallo pinto con tortilla de maíz', 7, 'El desayuno tico de siempre, naturalmente sin gluten.', 20, 4, 'Fácil', 1200, 220, 6, 40, 44, 18, 2, '2026-10-25 08:00:00'),
  (2, 'Queque de zanahoria sin azúcar', 9, 'Endulzado con eritritol y harina de arroz: apto para diabetes y celiaquía.', 60, 10, 'Media', 2100, 290, 25, 18, 32, 120, 3, '2026-11-03 08:00:00'),
  (3, 'Picadillo de chayote con pollo', 8, 'Plato fuerte bajo en carbohidratos y sin alérgenos comunes.', 35, 4, 'Fácil', 900, 36, 14, 12, 110, 30, 2, '2026-11-07 08:00:00'),
  (4, 'Pudín de chía con bebida de avena', 10, 'Merienda sin lactosa, alta en fibra. Refrigerar toda la noche.', 10, 2, 'Fácil', 380, 38, 4, 20, 10, 18, 3, '2026-11-01 08:00:00'),
  (5, 'Arepas de plátano maduro', 7, 'Sin harina de trigo; dulces de forma natural.', 15, 2, 'Fácil', 520, 72, 26, 5, 20, 16, 2, '2026-10-18 08:00:00');

INSERT INTO receta_ingredientes (receta_id, ingrediente_id, cantidad, orden) VALUES
  (1, 10, '2 tazas cocido', 1),
  (1, 9, '1 ½ tazas', 2),
  (1, 24, '½ unidad', 3),
  (1, 19, '2 cdas picado', 4),
  (1, 4, '4 tortillas', 5),
  (2, 1, '2 tazas', 1),
  (2, 12, '2 tazas rallada', 2),
  (2, 7, '3 unidades', 3),
  (2, 13, '¾ taza', 4),
  (2, 16, '½ taza', 5),
  (2, 23, '1 cdta', 6),
  (3, 17, '3 unidades', 1),
  (3, 18, '400 g desmenuzado', 2),
  (3, 24, '1 unidad', 3),
  (3, 19, 'al gusto', 4),
  (4, 20, '4 cdas', 1),
  (4, 6, '1 taza', 2),
  (4, 23, 'una pizca', 3),
  (4, 13, '1 cdta', 4),
  (5, 11, '1 unidad', 1),
  (5, 7, '2 unidades', 2),
  (5, 1, '3 cdas', 3),
  (5, 15, '1 cda (opcional)', 4);

INSERT INTO receta_pasos (receta_id, orden, texto) VALUES
  (1, 1, 'Sofría la cebolla en una cucharada de aceite.'),
  (1, 2, 'Agregue los frijoles con un poco de su caldo y deje reducir.'),
  (1, 3, 'Incorpore el arroz y mezcle hasta integrar.'),
  (1, 4, 'Termine con culantro picado y sirva con tortillas de maíz calientes.'),
  (2, 1, 'Precaliente el horno a 180 °C.'),
  (2, 2, 'Bata los huevos con el eritritol y el aceite de coco.'),
  (2, 3, 'Agregue la zanahoria y la canela.'),
  (2, 4, 'Incorpore la harina de arroz cernida.'),
  (2, 5, 'Hornee 40 minutos en molde engrasado.'),
  (3, 1, 'Pele y corte el chayote en cubos pequeños.'),
  (3, 2, 'Sofría la cebolla, agregue el pollo y el chayote.'),
  (3, 3, 'Tape y cocine 15 minutos a fuego bajo.'),
  (3, 4, 'Ajuste la sal y termine con culantro.'),
  (4, 1, 'Mezcle la chía con la bebida de avena.'),
  (4, 2, 'Agregue canela y endulzante.'),
  (4, 3, 'Refrigere al menos 4 horas.'),
  (5, 1, 'Machaque el plátano.'),
  (5, 2, 'Mezcle con huevo y harina de arroz.'),
  (5, 3, 'Cocine porciones pequeñas en sartén antiadherente.');

INSERT INTO receta_sustituciones (receta_id, original, sustituto, motivo) VALUES
  (1, 'Salsa inglesa', 'Salsa inglesa certificada sin gluten', 'Algunas marcas contienen malta de cebada.'),
  (2, 'Azúcar', 'Eritritol', 'Reduce los azúcares por porción sin afectar la glucosa.'),
  (2, 'Harina de trigo', 'Harina de arroz', 'Libre de gluten.'),
  (4, 'Leche de vaca', 'Bebida de avena sin gluten', 'Sin lactosa.'),
  (5, 'Mantequilla de maní', 'Semillas de chía', 'Para personas con alergia al maní.');

-- Comunidad
INSERT INTO resenas (id, tipo, ref_id, usuario_id, puntaje, comentario, creado_en) VALUES
  (1, 'producto', 1, 2, 5, 'Suave y no se desmorona. El mejor pan sin gluten que he probado en el país.', '2026-11-01 08:00:00'),
  (2, 'producto', 1, 3, 4, 'Buen sabor; tostado queda excelente.', '2026-11-04 08:00:00'),
  (3, 'receta', 2, 2, 5, 'Quedó húmedo y nadie notó que era sin azúcar.', '2026-11-05 08:00:00'),
  (4, 'restaurante', 101, 2, 5, 'Cocina realmente separada; el personal conoce la celiaquía.', '2026-10-29 08:00:00'),
  (5, 'restaurante', 102, 3, 4, 'Muchas opciones sin lácteos y con información nutricional.', '2026-11-02 08:00:00'),
  (6, 'restaurante', 105, 3, 5, 'Indican los carbohidratos de cada postre. ¡Muy útil!', '2026-11-06 08:00:00');

-- Propuestas de la comunidad (ocultas hasta su aprobación)
INSERT INTO productos (id, nombre, marca, categoria_id, precio, porcion_g, calorias, carbohidratos, azucares, fibra, proteina, grasa, sodio, ingredientes, publicado, creado_en) VALUES
  (11, 'Galletas de arroz sabor natural', 'Crujitos', 3, 1350, 18, 70, 15, 0, 0.5, 1.5, 0.5, 30, 'Arroz integral, sal.', 0, '2026-11-09 08:00:00');

INSERT INTO producto_tiendas (producto_id, establecimiento_id) VALUES
  (11, 2);

INSERT INTO establecimientos (id, nombre, tipo, provincia, direccion, telefono, tipo_comida, seguridad, info_nutricional, publicado) VALUES
  (106, 'Soda El Maicito', 'restaurante', 'Limón', 'Limón centro (dirección sin verificar)', '', 'Comida típica', 'opciones_adaptadas', 0, 0);

INSERT INTO aportes (id, usuario_id, tipo, entidad, ref_id, corrige_id, titulo, descripcion, estado, motivo, creado_en) VALUES
  (1, 2, 'producto', NULL, 11, NULL, 'Galletas de arroz sabor natural', '', 'pendiente', '', '2026-11-09 08:00:00'),
  (2, 3, 'reporte', 'producto', 1, NULL, 'Precio desactualizado: Pan de molde sin gluten', 'En La Cosecha de Curridabat ahora cuesta ₡3 990.', 'pendiente', '', '2026-11-10 08:00:00'),
  (3, 2, 'restaurante', NULL, 106, NULL, 'Soda El Maicito', '', 'rechazado', 'No se pudo verificar la dirección ni el teléfono.', '2026-10-20 08:00:00'),
  (4, 3, 'receta', NULL, 4, NULL, 'Pudín de chía con bebida de avena', '', 'aprobado', '', '2026-10-31 08:00:00');

INSERT INTO favoritos (usuario_id, tipo, ref_id) VALUES
  (3, 'producto', 2),
  (3, 'receta', 2),
  (3, 'restaurante', 105);

INSERT INTO lista_compras (usuario_id, texto, origen, hecho) VALUES
  (3, 'Bebida de avena sin azúcar', 'Producto', 0);

-- Primeros pasos
INSERT INTO guias (clave, titulo, resumen, orden) VALUES
  ('celiaquia', 'Enfermedad celíaca', 'Una dieta sin gluten estricta y de por vida.', 1),
  ('diabetes', 'Diabetes', 'Conteo de carbohidratos, porciones y elecciones informadas.', 2),
  ('alergias', 'Alergias e intolerancias', 'Identificar alérgenos y evitar el contacto cruzado.', 3);

INSERT INTO guia_secciones (id, guia_clave, orden, titulo) VALUES
  (1, 'celiaquia', 1, 'Cómo leer etiquetas'),
  (2, 'celiaquia', 2, 'Qué evitar o vigilar'),
  (3, 'celiaquia', 3, 'Organizar la cocina'),
  (4, 'celiaquia', 4, 'Comer fuera de casa'),
  (5, 'diabetes', 1, 'Cómo leer etiquetas'),
  (6, 'diabetes', 2, 'Qué vigilar'),
  (7, 'diabetes', 3, 'Organizar la cocina'),
  (8, 'diabetes', 4, 'Comer fuera de casa'),
  (9, 'alergias', 1, 'Cómo leer etiquetas'),
  (10, 'alergias', 2, 'Qué evitar o vigilar'),
  (11, 'alergias', 3, 'Organizar la cocina'),
  (12, 'alergias', 4, 'Comer fuera de casa');

INSERT INTO guia_puntos (seccion_id, orden, texto) VALUES
  (1, 1, 'Busque trigo, cebada, centeno, malta y avena no certificada en la lista de ingredientes.'),
  (1, 2, 'Revise las leyendas "puede contener" o "elaborado en líneas que procesan trigo".'),
  (1, 3, 'Prefiera productos con sello o declaración "sin gluten".'),
  (2, 1, 'Pan, pastas, galletas y empanizados de trigo.'),
  (2, 2, 'Salsas espesadas con harina, salsa de soya y salsa inglesa.'),
  (2, 3, 'Cervezas de malta.'),
  (3, 1, 'Use tostadora, tablas y coladores exclusivos.'),
  (3, 2, 'Guarde los productos sin gluten en estantes superiores.'),
  (3, 3, 'Limpie superficies antes de cocinar.'),
  (4, 1, 'Pregunte por cocina separada y aceite exclusivo para freír.'),
  (4, 2, 'Consulte el directorio de restaurantes con nivel de seguridad.'),
  (4, 3, 'Lleve una tarjeta que explique su condición.'),
  (5, 1, 'Fíjese primero en el tamaño de la porción.'),
  (5, 2, 'Revise carbohidratos totales y azúcares por porción.'),
  (5, 3, 'La fibra ayuda a moderar la absorción de los carbohidratos.'),
  (6, 1, 'Bebidas azucaradas, refrescos naturales con azúcar añadida y repostería.'),
  (6, 2, 'Porciones grandes de arroz, pan y tortillas.'),
  (6, 3, 'Productos "light" que no reducen carbohidratos.'),
  (7, 1, 'Use el método del plato: ½ vegetales, ¼ proteína, ¼ carbohidratos.'),
  (7, 2, 'Tenga a mano medidores de taza y una balanza.'),
  (7, 3, 'Planifique meriendas con proteína y fibra.'),
  (8, 1, 'Pida el casado con más ensalada y menos arroz.'),
  (8, 2, 'Prefiera restaurantes que publican información nutricional.'),
  (8, 3, 'Defina con su profesional de salud sus rangos personales.'),
  (9, 1, 'Los alérgenos suelen aparecer en negrita o en la sección "Contiene".'),
  (9, 2, 'Revise cada compra: las fórmulas cambian.'),
  (9, 3, 'Atención a nombres alternos: caseína y suero (leche), albúmina (huevo).'),
  (10, 1, 'Productos a granel sin etiqueta.'),
  (10, 2, 'Salsas, aderezos y postres de origen desconocido.'),
  (10, 3, 'Leyendas de trazas cuando la alergia es severa.'),
  (11, 1, 'Rotule recipientes.'),
  (11, 2, 'Cocine primero los platos sin alérgenos.'),
  (11, 3, 'Lave utensilios con agua y jabón.'),
  (12, 1, 'Informe su alergia al ordenar, no solo al mesero sino a cocina.'),
  (12, 2, 'Lleve consigo la medicación indicada por su médico.'),
  (12, 3, 'Consulte las reseñas de otras personas con su misma restricción.');

INSERT INTO sustituciones (grupo, original, sustituto, uso, aplica_a) VALUES
  ('Harinas', 'Harina de trigo', 'Harina de arroz + fécula de yuca (70/30)', 'Queques, panes y repostería', 'gluten'),
  ('Harinas', 'Pan rallado', 'Harina de maíz o tortilla molida', 'Empanizados', 'gluten'),
  ('Harinas', 'Pasta de trigo', 'Pasta de arroz o de maíz', 'Platos fuertes', 'gluten'),
  ('Lácteos', 'Leche de vaca', 'Bebida de avena, arroz o coco', 'Batidos, café, repostería', 'lactosa'),
  ('Lácteos', 'Natilla', 'Yogur griego deslactosado', 'Acompañamiento de gallo pinto', 'lactosa'),
  ('Lácteos', 'Mantequilla', 'Aceite de coco', 'Horneado', 'lactosa'),
  ('Azúcares', 'Azúcar blanca', 'Eritritol o stevia', 'Bebidas y repostería', 'diabetes'),
  ('Azúcares', 'Tapa de dulce', 'Canela y vainilla para aromatizar', 'Agua dulce, café', 'diabetes'),
  ('Azúcares', 'Arroz blanco', 'Arroz de coliflor o mitad arroz / mitad vegetales', 'Casado', 'diabetes'),
  ('Otros', 'Huevo (para ligar)', '1 cda de chía + 3 cdas de agua', 'Queques y panqueques', 'huevo'),
  ('Otros', 'Mantequilla de maní', 'Pasta de semillas de girasol', 'Untables y batidos', 'mani');

