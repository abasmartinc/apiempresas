import unittest
from validar_cif import validar_cif, validar_dni, validar_nie, validar_nif, tipo_nif, tipo_entidad


class TestValidarCif(unittest.TestCase):
    def test_cif(self):
        self.assertTrue(validar_cif("A46103834"))
        self.assertTrue(validar_cif("a-46.103.834"))
        self.assertTrue(validar_cif("Q2826000H"))
        self.assertFalse(validar_cif("A46103835"))
        self.assertFalse(validar_cif("Q2826000I"))
        self.assertFalse(validar_cif("B1234567"))
        self.assertFalse(validar_cif("12345678Z"))
        self.assertFalse(validar_cif(""))
        self.assertFalse(validar_cif(None))

    def test_dni_nie(self):
        self.assertTrue(validar_dni("12345678Z"))
        self.assertFalse(validar_dni("12345678A"))
        self.assertTrue(validar_nie("X1234567L"))
        self.assertTrue(validar_nie("Y1234567X"))
        self.assertTrue(validar_nie("Z1234567R"))
        self.assertFalse(validar_nie("X1234567A"))

    def test_nif_y_tipos(self):
        self.assertTrue(validar_nif("A46103834"))
        self.assertTrue(validar_nif("12345678Z"))
        self.assertTrue(validar_nif("X1234567L"))
        self.assertFalse(validar_nif("ZZZ"))
        self.assertEqual(tipo_nif("A46103834"), "cif")
        self.assertEqual(tipo_nif("12345678Z"), "dni")
        self.assertEqual(tipo_nif("X1234567L"), "nie")
        self.assertIsNone(tipo_nif("A46103835"))
        self.assertEqual(tipo_entidad("B00000000"), "Sociedad de responsabilidad limitada")
        self.assertIsNone(tipo_entidad("A46103835"))


if __name__ == "__main__":
    unittest.main()
