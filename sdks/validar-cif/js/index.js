'use strict';
/**
 * validar-cif: valida CIF, DNI y NIE españoles (formato y dígito de control).
 * Sin dependencias. https://apiempresas.es/validar-cif-php-python
 *
 * Ojo: un CIF bien formado no garantiza que la empresa exista. Para saber si existe,
 * su razón social y si sigue activa: https://apiempresas.es (API de empresas).
 */

const LETRAS_DNI = 'TRWAGMYFPDXBNJZSQVHLCKE';
const LETRAS_CONTROL_CIF = 'JABCDEFGHI';

/** Forma jurídica de cada letra inicial del CIF. */
const TIPOS_ENTIDAD = {
  A: 'Sociedad anónima',
  B: 'Sociedad de responsabilidad limitada',
  C: 'Sociedad colectiva',
  D: 'Sociedad comanditaria',
  E: 'Comunidad de bienes, herencia yacente y otras entidades sin personalidad jurídica',
  F: 'Sociedad cooperativa',
  G: 'Asociación',
  H: 'Comunidad de propietarios en régimen de propiedad horizontal',
  J: 'Sociedad civil, con o sin personalidad jurídica',
  N: 'Entidad extranjera',
  P: 'Corporación local',
  Q: 'Organismo público',
  R: 'Congregación o institución religiosa',
  S: 'Órgano de la Administración del Estado y de las comunidades autónomas',
  U: 'Unión temporal de empresas',
  V: 'Otros tipos no definidos',
  W: 'Establecimiento permanente de entidad no residente en España',
};

/** Quita espacios, guiones, puntos y barras, y pasa a mayúsculas. */
function limpiar(valor) {
  return String(valor == null ? '' : valor).replace(/[\s.\-\/]/g, '').toUpperCase();
}

/** true si el CIF (empresa) está bien formado y el carácter de control cuadra. */
function validarCif(valor) {
  const cif = limpiar(valor);
  const m = cif.match(/^([ABCDEFGHJNPQRSUVW])(\d{7})([0-9A-J])$/);
  if (!m) return false;
  const [, letra, numeros, control] = m;

  let suma = 0;
  for (let i = 0; i < 7; i++) {
    let n = Number(numeros[i]);
    if (i % 2 === 0) {          // posiciones impares: se dobla y se suman sus cifras
      n *= 2;
      if (n > 9) n -= 9;
    }
    suma += n;
  }
  const digito = (10 - (suma % 10)) % 10;
  const letraControl = LETRAS_CONTROL_CIF[digito];

  if ('ABEH'.includes(letra)) return control === String(digito);    // control numérico
  if ('NPQRSW'.includes(letra)) return control === letraControl;    // control con letra
  return control === String(digito) || control === letraControl;
}

/** true si el DNI (8 números y letra) es correcto. */
function validarDni(valor) {
  const dni = limpiar(valor);
  const m = dni.match(/^(\d{8})([A-Z])$/);
  return !!m && LETRAS_DNI[Number(m[1]) % 23] === m[2];
}

/** true si el NIE (X, Y o Z, 7 números y letra) es correcto. */
function validarNie(valor) {
  const nie = limpiar(valor);
  const m = nie.match(/^([XYZ])(\d{7})([A-Z])$/);
  if (!m) return false;
  const numero = Number('XYZ'.indexOf(m[1]) + m[2]);
  return LETRAS_DNI[numero % 23] === m[3];
}

/** true si es un CIF, DNI o NIE válido. */
function validarNif(valor) {
  return validarCif(valor) || validarDni(valor) || validarNie(valor);
}

/**
 * Qué es el valor: 'cif', 'dni', 'nie' o null si no es válido.
 * Útil para decidir si consultar la empresa (solo los CIF están en el Registro Mercantil).
 */
function tipoNif(valor) {
  if (validarCif(valor)) return 'cif';
  if (validarDni(valor)) return 'dni';
  if (validarNie(valor)) return 'nie';
  return null;
}

/** Forma jurídica según la letra inicial del CIF, o null. */
function tipoEntidad(valor) {
  const cif = limpiar(valor);
  return validarCif(cif) ? TIPOS_ENTIDAD[cif[0]] : null;
}

module.exports = { validarCif, validarDni, validarNie, validarNif, tipoNif, tipoEntidad, limpiar, TIPOS_ENTIDAD };
