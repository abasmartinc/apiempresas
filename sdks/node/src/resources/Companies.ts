import { ApiEmpresas } from '../ApiEmpresas';
import { 
  BaseResponse, 
  Company, 
  BatchRequest, 
  BatchResponse, 
  ScoreData, 
  BormeData, 
  SignalsData 
} from '../types';

export class Companies {
  constructor(private client: ApiEmpresas) {}

  /**
   * Obtiene los datos básicos de una empresa por su CIF.
   */
  public async get(cif: string): Promise<Company> {
    const params = new URLSearchParams({ cif });
    const response = await this.client.request<BaseResponse<Company>>(`/companies?${params.toString()}`);
    return response.data!;
  }

  /**
   * Busca empresas por nombre o razón social.
   */
  public async search(q: string): Promise<Company> {
    const params = new URLSearchParams({ q });
    const response = await this.client.request<BaseResponse<Company>>(`/companies/search?${params.toString()}`);
    return response.data!;
  }

  /**
   * Consulta múltiple de CIFs en una sola petición.
   */
  public async batch(request: BatchRequest): Promise<{ meta: BatchResponse, data: Company[] }> {
    const response = await this.client.request<{ success: boolean, meta: BatchResponse, data: Company[] }>('/companies/batch', {
      method: 'POST',
      body: JSON.stringify(request)
    });
    return { meta: response.meta, data: response.data };
  }

  /**
   * (Pro) Obtiene el Scoring Comercial de una empresa.
   */
  public async score(cif: string): Promise<ScoreData> {
    const params = new URLSearchParams({ cif });
    const response = await this.client.request<BaseResponse<ScoreData>>(`/companies/score?${params.toString()}`);
    return response.data!;
  }

  /**
   * (Pro) Obtiene el historial de actos del BORME de una empresa.
   */
  public async borme(cif: string): Promise<BormeData> {
    const params = new URLSearchParams({ cif });
    const response = await this.client.request<BaseResponse<BormeData>>(`/companies/borme?${params.toString()}`);
    return response.data!;
  }

  /**
   * (Pro) Obtiene las señales societarias recientes de una empresa.
   */
  public async signals(cif: string): Promise<SignalsData> {
    const params = new URLSearchParams({ cif });
    const response = await this.client.request<BaseResponse<SignalsData>>(`/companies/signals?${params.toString()}`);
    return response.data!;
  }

  /**
   * (Business) Obtiene Insights IA de una empresa.
   */
  public async insights(cif: string): Promise<any> {
    const params = new URLSearchParams({ cif });
    const response = await this.client.request<BaseResponse<any>>(`/companies/insights?${params.toString()}`);
    return response.data!;
  }

  /**
   * (Pro) Obtiene datos de contacto y preparación de la empresa.
   */
  public async contactPrep(cif: string): Promise<any> {
    const params = new URLSearchParams({ cif });
    const response = await this.client.request<BaseResponse<any>>(`/companies/contact-prep?${params.toString()}`);
    return response.data!;
  }

  /**
   * (Business) Obtiene la información del Radar de empresas.
   */
  public async radar(cif: string): Promise<any> {
    const params = new URLSearchParams({ cif });
    const response = await this.client.request<BaseResponse<any>>(`/companies/radar?${params.toString()}`);
    return response.data!;
  }

  /**
   * (Business) Realiza un match avanzado con los datos de una empresa.
   */
  public async match(cif: string): Promise<any> {
    const params = new URLSearchParams({ cif });
    const response = await this.client.request<BaseResponse<any>>(`/companies/match?${params.toString()}`);
    return response.data!;
  }

  /**
   * (Business) Obtiene la red o entramado societario (Network) de una empresa.
   */
  public async network(cif: string): Promise<any> {
    const params = new URLSearchParams({ cif });
    const response = await this.client.request<BaseResponse<any>>(`/companies/network?${params.toString()}`);
    return response.data!;
  }
}
