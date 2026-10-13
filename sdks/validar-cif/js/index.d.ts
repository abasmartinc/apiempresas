/** true si el CIF (empresa) está bien formado y el carácter de control cuadra. */
export function validarCif(valor: string): boolean;
/** true si el DNI (8 números y letra) es correcto. */
export function validarDni(valor: string): boolean;
/** true si el NIE (X, Y o Z, 7 números y letra) es correcto. */
export function validarNie(valor: string): boolean;
/** true si es un CIF, DNI o NIE válido. */
export function validarNif(valor: string): boolean;
/** 'cif', 'dni', 'nie' o null si no es válido. */
export function tipoNif(valor: string): 'cif' | 'dni' | 'nie' | null;
/** Forma jurídica según la letra inicial del CIF, o null. */
export function tipoEntidad(valor: string): string | null;
/** Quita espacios, guiones, puntos y barras, y pasa a mayúsculas. */
export function limpiar(valor: string): string;
export const TIPOS_ENTIDAD: Record<string, string>;
