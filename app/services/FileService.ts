import { type IFileProvider } from '~/models/files';

export class FileService implements IFileProvider {
  async getFileBlob(id: number): Promise<Blob> {
    const client = useSanctumClient();
    const response = await client<Blob>(`/api/admin/files/${id}/download`, {
      method: "GET",
      // @ts-ignore - useSanctumClient підтримує responseType через ofetch
      responseType: "blob",
    });
    return response;
  }
}
