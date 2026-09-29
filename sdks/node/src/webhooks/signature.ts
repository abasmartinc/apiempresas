import { createHmac, timingSafeEqual } from 'crypto';
import { WebhookPayload } from '../types';

/**
 * Comprueba la cabecera X-ApiEmpresas-Signature (t=<timestamp>,v1=<hex>).
 * La firma es HMAC-SHA256(secret, `${t}.${cuerpo}`) sobre el cuerpo tal cual llega:
 * pásale el cuerpo en bruto (string o Buffer), no el JSON ya parseado.
 *
 * @param toleranceSeconds antigüedad máxima aceptada (300 por defecto; 0 = sin límite).
 */
export function verifyWebhookSignature(
  rawBody: string | Buffer,
  signatureHeader: string | null | undefined,
  secret: string,
  toleranceSeconds: number = 300,
  now: number = Math.floor(Date.now() / 1000)
): boolean {
  if (!signatureHeader || !secret) return false;
  let t: string | undefined;
  const v1: string[] = [];
  for (const part of signatureHeader.split(',')) {
    const i = part.indexOf('=');
    if (i < 0) continue;
    const k = part.slice(0, i).trim();
    const v = part.slice(i + 1).trim();
    if (k === 't') t = v;
    else if (k === 'v1') v1.push(v);
  }
  if (!t || !/^\d+$/.test(t) || v1.length === 0) return false;
  if (toleranceSeconds > 0 && Math.abs(now - parseInt(t, 10)) > toleranceSeconds) return false;

  const body = typeof rawBody === 'string' ? rawBody : rawBody.toString('utf8');
  const expected = Buffer.from(createHmac('sha256', secret).update(`${t}.${body}`).digest('hex'), 'utf8');
  return v1.some((sig) => {
    const got = Buffer.from(sig, 'utf8');
    return got.length === expected.length && timingSafeEqual(got, expected);
  });
}

/**
 * Comprueba la firma y devuelve el evento parseado. Lanza un Error si la firma no es válida.
 */
export function constructWebhookEvent<T = any>(
  rawBody: string | Buffer,
  signatureHeader: string | null | undefined,
  secret: string,
  toleranceSeconds: number = 300
): WebhookPayload<T> {
  if (!verifyWebhookSignature(rawBody, signatureHeader, secret, toleranceSeconds)) {
    throw new Error('Firma del webhook no válida o caducada.');
  }
  const body = typeof rawBody === 'string' ? rawBody : rawBody.toString('utf8');
  return JSON.parse(body) as WebhookPayload<T>;
}
