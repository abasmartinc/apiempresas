"""validar-cif: valida CIF, DNI y NIE españoles (formato y dígito de control).

Sin dependencias. https://apiempresas.es/validar-cif-php-python

Ojo: un CIF bien formado no garantiza que la empresa exista. Para saber si existe,
su razón social y si sigue activa: https://apiempresas.es (API de empresas).
"""
import re
from typing import Optional

__version__ = "1.0.0"
__all__ = ["validar_cif", "validar_dni", "validar_nie", "validar_nif", "tipo_nif", "tipo_entidad", "limpiar", "TIPOS_ENTIDAD"]

LETRAS_DNI = "TRWAGMYFPDXBNJZSQVHLCKE"
LETRAS_CONTROL_CIF = "JABCDEFGHI"

#: Forma jurídica de cada letra inicial del CIF.
TIPOS_ENTIDAD = {
    "A": "Sociedad anónima",
    "B": "Sociedad de responsabilidad limitada",
    "C": "Sociedad colectiva",
    "D": "Sociedad comanditaria",
    "E": "Comunidad de bienes, herencia yacente y otras entidades sin personalidad jurídica",
    "F": "Sociedad cooperativa",
    "G": "Asociación",
    "H": "Comunidad de propietarios en régimen de propiedad horizontal",
    "J": "Sociedad civil, con o sin personalidad jurídica",
    "N": "Entidad extranjera",
    "P": "Corporación local",
    "Q": "Organismo público",
    "R": "Congregación o institución religiosa",
    "S": "Órgano de la Administración del Estado y de las comunidades autónomas",
    "U": "Unión temporal de empresas",
    "V": "Otros tipos no definidos",
    "W": "Establecimiento permanente de entidad no residente en España",
}


def limpiar(valor) -> str:
    """Quita espacios, guiones, puntos y barras, y pasa a mayúsculas."""
    return re.sub(r"[\s.\-/]", "", "" if valor is None else str(valor)).upper()


def validar_cif(valor) -> bool:
    """True si el CIF (empresa) está bien formado y el carácter de control cuadra."""
    m = re.fullmatch(r"([ABCDEFGHJNPQRSUVW])(\d{7})([0-9A-J])", limpiar(valor))
    if not m:
        return False
    letra, numeros, control = m.groups()

    suma = 0
    for i, c in enumerate(numeros):
        n = int(c)
        if i % 2 == 0:  # posiciones impares: se dobla y se suman sus cifras
            n *= 2
            n = n - 9 if n > 9 else n
        suma += n
    digito = (10 - suma % 10) % 10
    letra_control = LETRAS_CONTROL_CIF[digito]

    if letra in "ABEH":
        return control == str(digito)  # control numérico
    if letra in "NPQRSW":
        return control == letra_control  # control con letra
    return control in (str(digito), letra_control)


def validar_dni(valor) -> bool:
    """True si el DNI (8 números y letra) es correcto."""
    m = re.fullmatch(r"(\d{8})([A-Z])", limpiar(valor))
    return bool(m) and LETRAS_DNI[int(m.group(1)) % 23] == m.group(2)


def validar_nie(valor) -> bool:
    """True si el NIE (X, Y o Z, 7 números y letra) es correcto."""
    m = re.fullmatch(r"([XYZ])(\d{7})([A-Z])", limpiar(valor))
    if not m:
        return False
    numero = int(str("XYZ".index(m.group(1))) + m.group(2))
    return LETRAS_DNI[numero % 23] == m.group(3)


def validar_nif(valor) -> bool:
    """True si es un CIF, DNI o NIE válido."""
    return validar_cif(valor) or validar_dni(valor) or validar_nie(valor)


def tipo_nif(valor) -> Optional[str]:
    """'cif', 'dni', 'nie' o None si no es válido."""
    if validar_cif(valor):
        return "cif"
    if validar_dni(valor):
        return "dni"
    if validar_nie(valor):
        return "nie"
    return None


def tipo_entidad(valor) -> Optional[str]:
    """Forma jurídica según la letra inicial del CIF, o None."""
    cif = limpiar(valor)
    return TIPOS_ENTIDAD[cif[0]] if validar_cif(cif) else None
