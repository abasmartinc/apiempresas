<?php

declare(strict_types=1);

use ApiEmpresas\ValidarCif\Validador;
use PHPUnit\Framework\TestCase;

final class ValidadorTest extends TestCase
{
    public function testCif(): void
    {
        $this->assertTrue(Validador::cif('A46103834'));
        $this->assertTrue(Validador::cif('a-46.103.834'));
        $this->assertTrue(Validador::cif('Q2826000H'));
        $this->assertFalse(Validador::cif('A46103835'));
        $this->assertFalse(Validador::cif('Q2826000I'));
        $this->assertFalse(Validador::cif('B1234567'));
        $this->assertFalse(Validador::cif('12345678Z'));
        $this->assertFalse(Validador::cif(''));
        $this->assertFalse(Validador::cif(null));
    }

    public function testDniNie(): void
    {
        $this->assertTrue(Validador::dni('12345678Z'));
        $this->assertFalse(Validador::dni('12345678A'));
        $this->assertTrue(Validador::nie('X1234567L'));
        $this->assertTrue(Validador::nie('Y1234567X'));
        $this->assertTrue(Validador::nie('Z1234567R'));
        $this->assertFalse(Validador::nie('X1234567A'));
    }

    public function testNifYTipos(): void
    {
        $this->assertTrue(Validador::nif('A46103834'));
        $this->assertTrue(Validador::nif('12345678Z'));
        $this->assertTrue(Validador::nif('X1234567L'));
        $this->assertFalse(Validador::nif('ZZZ'));
        $this->assertSame('cif', Validador::tipo('A46103834'));
        $this->assertSame('dni', Validador::tipo('12345678Z'));
        $this->assertSame('nie', Validador::tipo('X1234567L'));
        $this->assertNull(Validador::tipo('A46103835'));
        $this->assertSame('Sociedad de responsabilidad limitada', Validador::tipoEntidad('B00000000'));
        $this->assertNull(Validador::tipoEntidad('A46103835'));
    }
}
