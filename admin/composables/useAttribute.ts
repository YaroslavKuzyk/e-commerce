import { AttributeService } from '~/services/AttributeService';
import type { AttributeFilters, AttributeFormData, AttributeValueFormData } from '~/models/attribute';

export const useAttribute = () => {
  const provider = new AttributeService();

  return {
    getAllAttributes: (filters?: AttributeFilters) => provider.getAllAttributes(filters),
    getAllAttributesPromise: (filters?: AttributeFilters) => provider.getAllAttributesPromise(filters),
    getAttributeById: (id: number) => provider.getAttributeById(id),
    createAttribute: (payload: AttributeFormData) => provider.createAttribute(payload),
    updateAttribute: (id: number, payload: Partial<AttributeFormData>) => provider.updateAttribute(id, payload),
    deleteAttribute: (id: number) => provider.deleteAttribute(id),
    generateSlug: (name: string) => provider.generateSlug(name),
    addValue: (attributeId: number, payload: AttributeValueFormData) => provider.addValue(attributeId, payload),
    updateValue: (attributeId: number, valueId: number, payload: Partial<AttributeValueFormData>) => provider.updateValue(attributeId, valueId, payload),
    deleteValue: (attributeId: number, valueId: number) => provider.deleteValue(attributeId, valueId),
  };
};
