# Optimización de consultas con fechas en `Admin/index.php`

Se identificaron consultas que realizan filtros o `ORDER BY` sobre columnas de fecha aplicando la función `DATE()`. El uso de funciones sobre las columnas evita que los índices puedan ser utilizados y obliga a evaluar fila por fila. A continuación, se resumen los casos y una forma típica de optimizarlos.

## Consultas detectadas

1. **Capacidad de bodegas diarias** (`gaf_capacidadbodegasdiaria`).
   * Consulta actual: usa `WHERE date(Fecha) BETWEEN '$FechaHace9Dias' AND '$FechaActual'` y `ORDER BY date(Fecha)`.
   * Ubicación: líneas ~1378–1403 del archivo.

2. **Toneladas de producción ingresadas** (`asignaciones`).
   * Consulta actual: usa `date(FechaColocado)` en el `SELECT`, `WHERE` y `GROUP BY` para agrupar por día.
   * Ubicación: líneas ~1538–1561 del archivo.

3. **Despachos y picking** (`despachos`, `detalle_piking`).
   * Consulta actual: el filtro `WHERE DATE(D.FechaRealizado) BETWEEN ...` y `where date(DS.Fecha_Hora_Despacho) BETWEEN ...` se combina con `GROUP BY Fecha` usando `DATE(...)`.
   * Ubicación: líneas ~1581–1637 del archivo.

## Recomendaciones generales

* **Evitar `DATE(columna)` en filtros**: en lugar de `DATE(columna) BETWEEN '2024-01-01' AND '2024-01-31'`, calcular los límites completos y filtrar con comparaciones directas, por ejemplo:
  ```sql
  columna >= '$inicio 00:00:00' AND columna < DATE_ADD('$fin', INTERVAL 1 DAY)
  ```
  Esto permite usar índices sobre la columna original.

* **Ordenar y agrupar usando la columna cruda**: si se necesita mostrar solo la fecha, devolverla formateada, pero ordenar por la columna sin función (`ORDER BY Fecha` si es `DATETIME`). Para agrupaciones diarias frecuentes, puede considerarse una columna generada `fecha_dia` indexada.

* **Reutilizar rangos de fecha precomputados**: definir `$inicioDia` y `$finDia` en PHP (incluyendo las horas) y usarlos en todas las consultas evita recalcular y garantiza consistencia.

* **Validar índices**: verificar que las columnas `Fecha`, `FechaColocado`, `FechaRealizado` y `Fecha_Hora_Despacho` tengan índices (individuales o compuestos con otras columnas usadas en filtros) para acelerar los rangos.

Aplicando estas prácticas, los filtros de fecha aprovecharán los índices existentes y reducirán los escaneos completos de tabla en `Admin/index.php`.
