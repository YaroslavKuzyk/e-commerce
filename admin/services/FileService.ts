import {
  type IFile,
  type IFileProvider,
  type IFileUploadPayload,
  type IFileFilters,
  type IFilesListResponse,
  type IFileResponse,
} from "~/models/files";

export class FileService implements IFileProvider {
  async getFiles(filters?: IFileFilters): Promise<IFilesListResponse> {
    const client = useSanctumClient();

    // Build query parameters
    const params = new URLSearchParams();
    if (filters?.search) {
      params.append('search', filters.search);
    }
    if (filters?.user_search) {
      params.append('user_search', filters.user_search);
    }
    if (filters?.types && filters.types.length > 0) {
      params.append('types', filters.types.join(','));
    }
    if (filters?.page) {
      params.append('page', String(filters.page));
    }
    if (filters?.per_page) {
      params.append('per_page', String(filters.per_page));
    }

    const queryString = params.toString();
    const url = queryString ? `/api/admin/files?${queryString}` : '/api/admin/files';

    const response = await client<IFilesListResponse>(url, {
      method: "GET",
    });
    return response;
  }

  async getFile(id: number): Promise<IFile> {
    const client = useSanctumClient();
    const response = await client<IFileResponse>(`/api/admin/files/${id}`, {
      method: "GET",
    });
    return response.data;
  }

  async uploadFile(payload: IFileUploadPayload): Promise<IFile> {
    const client = useSanctumClient();
    const formData = new FormData();
    formData.append("file", payload.file);

    const response = await client<IFileResponse>("/api/admin/files", {
      method: "POST",
      body: formData,
    });
    return response.data;
  }

  async deleteFile(id: number): Promise<void> {
    const client = useSanctumClient();
    await client(`/api/admin/files/${id}`, {
      method: "DELETE",
    });
  }

  async deleteFiles(ids: number[]): Promise<void> {
    const client = useSanctumClient();
    await client('/api/admin/files/bulk-delete', {
      method: "POST",
      body: { ids },
    });
  }

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
