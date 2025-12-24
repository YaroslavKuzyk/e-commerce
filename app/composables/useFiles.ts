import { FileService } from '~/services/FileService';
import { type IFileProvider } from '~/models/files';

const provider: IFileProvider = new FileService();

export const useFiles = () => {
  return {
    getFileBlob: (id: number) => provider.getFileBlob(id),
  };
};
