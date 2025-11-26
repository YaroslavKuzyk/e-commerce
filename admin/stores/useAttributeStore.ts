import { defineStore } from 'pinia';
import type { AttributeFormData, AttributeFilters, AttributeValueFormData } from '~/models/attribute';

export const useAttributeStore = defineStore('attribute', () => {
  const {
    getAllAttributes,
    getAllAttributesPromise,
    getAttributeById,
    createAttribute,
    updateAttribute,
    deleteAttribute,
    generateSlug,
    addValue,
    updateValue,
    deleteValue,
  } = useAttribute();

  return {
    fetchAttributes: async (filters?: AttributeFilters) => await getAllAttributes(filters),
    fetchAttributesPromise: (filters?: AttributeFilters) => getAllAttributesPromise(filters),
    fetchAttributeById: async (id: number) => await getAttributeById(id),
    onCreateAttribute: async (payload: AttributeFormData) => await createAttribute(payload),
    onUpdateAttribute: async (id: number, payload: Partial<AttributeFormData>) => await updateAttribute(id, payload),
    onDeleteAttribute: async (id: number) => await deleteAttribute(id),
    onGenerateSlug: async (name: string) => await generateSlug(name),
    onAddValue: async (attributeId: number, payload: AttributeValueFormData) => await addValue(attributeId, payload),
    onUpdateValue: async (attributeId: number, valueId: number, payload: Partial<AttributeValueFormData>) => await updateValue(attributeId, valueId, payload),
    onDeleteValue: async (attributeId: number, valueId: number) => await deleteValue(attributeId, valueId),
  };
});
