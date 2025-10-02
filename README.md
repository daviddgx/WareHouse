# WareHouse
Mejoras a PDW

## Optimización de consultas por rango de fecha

* Todas las consultas de `Admin/index.php` que filtraban por fecha se actualizaron para utilizar comparaciones de rango (`>=` y `<`) con valores de fecha y hora completos. Esto evita aplicar funciones como `DATE()` sobre columnas indexadas y permite que el motor aproveche los índices existentes.
* Se agregaron variables comunes con las marcas de inicio (`00:00:00`) y fin exclusivo (inicio del día siguiente) para asegurar que los filtros cubran el periodo solicitado sin depender de conversiones en la base de datos.

### Índices recomendados

Verifica que existan índices en los siguientes campos para maximizar el beneficio del cambio:

```sql
SHOW INDEX FROM asignaciones WHERE Column_name IN ('FechaRegistro', 'FechaColocado');
SHOW INDEX FROM despachos WHERE Column_name IN ('FechaRealizado', 'Fecha_Hora_Despacho');
```

Si alguno no existe, crea un índice compuesto que acompañe los criterios más usados (por ejemplo, `Estado`, `Operador`) y el campo de fecha:

```sql
CREATE INDEX idx_asignaciones_estado_fecha ON asignaciones (Estado, FechaRegistro);
CREATE INDEX idx_despachos_operador_fecha ON despachos (Operador, Fecha_Hora_Despacho);
```

Documenta cualquier índice nuevo en este archivo para mantener la trazabilidad.
