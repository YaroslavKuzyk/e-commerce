import type {
  Attribute,
  AttributeFormData,
  AttributeFilters,
  AttributePaginatedResponse,
  AttributeValueFormData,
  IAttributeProvider,
} from '~/models/attribute';

export class AttributeService implements IAttributeProvider {
  getAllAttributes(filters?: AttributeFilters) {
    const client = useSanctumClient();

    const params = new URLSearchParams();
    if (filters?.name) params.append('name', filters.name);
    if (filters?.slug) params.append('slug', filters.slug);
    if (filters?.type) params.append('type', filters.type);
    if (filters?.status) params.append('status', filters.status);
    if (filters?.page) params.append('page', filters.page.toString());
    if (filters?.per_page) params.append('per_page', filters.per_page.toString());

    const queryString = params.toString();
    const url = `/api/admin/attributes${queryString ? `?${queryString}` : ''}`;

    return useAsyncData<AttributePaginatedResponse>(
      'attributes',
      async (): Promise<AttributePaginatedResponse> => {
        const res = await client<{ success: boolean; data: Attribute[]; meta?: AttributePaginatedResponse['meta'] }>(url);
        return {
          data: res.data,
          meta: res.meta,
        };
      }
    );
  }

  async getAllAttributesPromise(filters?: AttributeFilters): Promise<AttributePaginatedResponse> {
    const client = useSanctumClient();

    const params = new URLSearchParams();
    if (filters?.name) params.append('name', filters.name);
    if (filters?.slug) params.append('slug', filters.slug);
    if (filters?.type) params.append('type', filters.type);
    if (filters?.status) params.append('status', filters.status);
    if (filters?.page) params.append('page', filters.page.toString());
    if (filters?.per_page) params.append('per_page', filters.per_page.toString());

    const queryString = params.toString();
    const url = `/api/admin/attributes${queryString ? `?${queryString}` : ''}`;

    const res = await client<{ success: boolean; data: Attribute[]; meta?: AttributePaginatedResponse['meta'] }>(url);
    return {
      data: res.data,
      meta: res.meta,
    };
  }

  getAttributeById(id: number) {
    const client = useSanctumClient();

    return useAsyncData<Attribute>(
      `attribute-${id}`,
      () =>
        client<{ success: boolean; data: Attribute }>(
          `/api/admin/attributes/${id}`
        ).then((res) => res.data)
    );
  }

  createAttribute(payload: AttributeFormData) {
    const client = useSanctumClient();

    return useAsyncData<Attribute>(
      `attribute-create-${Date.now()}`,
      () =>
        client<{ success: boolean; data: Attribute }>(
          '/api/admin/attributes',
          {
            method: 'POST',
            body: payload,
          }
        ).then((res) => res.data)
    );
  }

  updateAttribute(id: number, payload: Partial<AttributeFormData>) {
    const client = useSanctumClient();

    return useAsyncData<Attribute>(
      `attribute-update-${id}-${Date.now()}`,
      () =>
        client<{ success: boolean; data: Attribute }>(
          `/api/admin/attributes/${id}`,
          {
            method: 'PUT',
            body: payload,
          }
        ).then((res) => res.data)
    );
  }

  deleteAttribute(id: number) {
    const client = useSanctumClient();

    return useAsyncData<{ success: boolean; message: string }>(
      `attribute-delete-${id}-${Date.now()}`,
      () =>
        client<{ success: boolean; message: string }>(
          `/api/admin/attributes/${id}`,
          {
            method: 'DELETE',
          }
        ).then((res) => res)
    );
  }

  generateSlug(name: string) {
    const client = useSanctumClient();

    return useAsyncData<{ slug: string }>(
      `attribute-slug-${Date.now()}`,
      () =>
        client<{ success: boolean; data: { slug: string } }>(
          '/api/admin/attributes/generate-slug',
          {
            method: 'POST',
            body: { name },
          }
        ).then((res) => res.data)
    );
  }

  addValue(attributeId: number, payload: AttributeValueFormData) {
    const client = useSanctumClient();

    return useAsyncData<Attribute>(
      `attribute-add-value-${attributeId}-${Date.now()}`,
      () =>
        client<{ success: boolean; data: Attribute }>(
          `/api/admin/attributes/${attributeId}/values`,
          {
            method: 'POST',
            body: payload,
          }
        ).then((res) => res.data)
    );
  }

  updateValue(attributeId: number, valueId: number, payload: Partial<AttributeValueFormData>) {
    const client = useSanctumClient();

    return useAsyncData<Attribute>(
      `attribute-update-value-${attributeId}-${valueId}-${Date.now()}`,
      () =>
        client<{ success: boolean; data: Attribute }>(
          `/api/admin/attributes/${attributeId}/values/${valueId}`,
          {
            method: 'PUT',
            body: payload,
          }
        ).then((res) => res.data)
    );
  }

  deleteValue(attributeId: number, valueId: number) {
    const client = useSanctumClient();

    return useAsyncData<{ success: boolean; message: string }>(
      `attribute-delete-value-${attributeId}-${valueId}-${Date.now()}`,
      () =>
        client<{ success: boolean; message: string }>(
          `/api/admin/attributes/${attributeId}/values/${valueId}`,
          {
            method: 'DELETE',
          }
        ).then((res) => res)
    );
  }
}
