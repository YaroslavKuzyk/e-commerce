export interface IFile {
  id: number;
  name: string;
  original_name: string;
  mime_type: string;
  size: number;
  path: string;
  user_id: number;
  user?: {
    id: number;
    name: string;
    email: string;
  };
  created_at: string;
  updated_at: string;
}

export interface IFileUploadPayload {
  file: File;
}

export interface IFileFilters {
  search?: string;
  user_search?: string;
  types?: string[];
}

export interface IFileProvider {
  getFiles(filters?: IFileFilters): Promise<IFile[]>;
  getFile(id: number): Promise<IFile>;
  uploadFile(payload: IFileUploadPayload): Promise<IFile>;
  deleteFile(id: number): Promise<void>;
  getFileBlob(id: number): Promise<Blob>;
}

export interface IFilesListResponse {
  success: boolean;
  data: IFile[];
}

export interface IFileResponse {
  success: boolean;
  data: IFile;
}
