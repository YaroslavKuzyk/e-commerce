export interface IFile {
  id: number;
  name: string;
  original_name: string;
  mime_type: string;
  size: number;
  path: string;
  created_at: string;
  updated_at: string;
}

export interface IFileProvider {
  getFileBlob(id: number): Promise<Blob>;
}
