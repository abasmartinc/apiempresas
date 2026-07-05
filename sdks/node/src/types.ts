export interface ApiEmpresasOptions {
  /**
   * Tu API Key de APIEmpresas.es
   */
  apiKey: string;
  /**
   * Base URL de la API. Por defecto es https://apiempresas.es/api/v1
   * Puedes sobreescribirla para apuntar al sandbox: https://apiempresas.es/api/sandbox/v1
   */
  baseURL?: string;
  /**
   * Timeout opcional para las peticiones en milisegundos.
   */
  timeout?: number;
}

export interface BaseResponse<T = any> {
  success: boolean;
  data?: T;
  error?: string;
  message?: string;
}

export interface Company {
  id?: number;
  cif: string;
  name: string;
  cnae?: string;
  cnae_label?: string;
  founded?: string;
  province?: string;
  municipality?: string;
  address?: string;
  status?: string;
  score?: number;
}

export interface BatchRequest {
  cifs: string[];
  admin?: boolean;
}

export interface BatchResponse {
  requested: number;
  found: number;
  cost: number;
  truncated: boolean;
}

export interface ScoreData {
  cif: string;
  score: number;
  priority: string;
  reasons: string[];
  last_signal?: {
    type: string;
    date: string;
  };
}

export interface BormeEvent {
  date: string;
  act_types: string;
  description: string;
  url_pdf: string;
}

export interface BormeData {
  cif: string;
  company_name: string;
  events: BormeEvent[];
}

export interface SignalEvent {
  type: string;
  label: string;
  date: string;
  probability: string;
}

export interface SignalsData {
  cif: string;
  signals: SignalEvent[];
}
