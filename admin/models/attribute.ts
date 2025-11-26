export type AttributeType = 'select' | 'multi_select' | 'checkbox' | 'switch' | 'color';
export type AttributeStatus = 'draft' | 'published';

export interface AttributeValue {
  id: number;
  attribute_id: number;
  value: string;
  slug: string;
  color_code: string | null;
  sort_order: number;
  created_at: string;
  updated_at: string;
}

export interface Attribute {
  id: number;
  name: string;
  slug: string;
  type: AttributeType;
  status: AttributeStatus;
  sort_order: number;
  values: AttributeValue[];
  created_at: string;
  updated_at: string;
}

export interface AttributeFormData {
  name: string;
  slug: string;
  type: AttributeType;
  status: AttributeStatus;
  sort_order?: number;
  values?: AttributeValueFormData[];
}

export interface AttributeValueFormData {
  value: string;
  slug: string;
  color_code?: string | null;
  sort_order?: number;
}

export interface AttributeFilters {
  name?: string;
  slug?: string;
  type?: AttributeType | null;
  status?: AttributeStatus | null;
  page?: number;
  per_page?: number;
}

export interface AttributePaginatedResponse {
  data: Attribute[];
  meta?: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
}

export interface IAttributeProvider {
  getAllAttributes: (filters?: AttributeFilters) => ReturnType<typeof useAsyncData<AttributePaginatedResponse | undefined>>;
  getAttributeById: (id: number) => ReturnType<typeof useAsyncData<Attribute>>;
  createAttribute: (payload: AttributeFormData) => ReturnType<typeof useAsyncData<Attribute>>;
  updateAttribute: (id: number, payload: Partial<AttributeFormData>) => ReturnType<typeof useAsyncData<Attribute>>;
  deleteAttribute: (id: number) => ReturnType<typeof useAsyncData<{ success: boolean; message: string }>>;
  generateSlug: (name: string) => ReturnType<typeof useAsyncData<{ slug: string }>>;
  addValue: (attributeId: number, payload: AttributeValueFormData) => ReturnType<typeof useAsyncData<Attribute>>;
  updateValue: (attributeId: number, valueId: number, payload: Partial<AttributeValueFormData>) => ReturnType<typeof useAsyncData<Attribute>>;
  deleteValue: (attributeId: number, valueId: number) => ReturnType<typeof useAsyncData<{ success: boolean; message: string }>>;
}
