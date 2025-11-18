import { FileService } from "~/services/FileService";
import { type IFileProvider, type IFileUploadPayload, type IFileFilters } from "~/models/files";

let provider: IFileProvider = new FileService();

export const useFiles = () => {
  return {
    getFiles: (filters?: IFileFilters) => provider.getFiles(filters),
    getFile: (id: number) => provider.getFile(id),
    uploadFile: (payload: IFileUploadPayload) => provider.uploadFile(payload),
    deleteFile: (id: number) => provider.deleteFile(id),
    getFileBlob: (id: number) => provider.getFileBlob(id),
  };
};
