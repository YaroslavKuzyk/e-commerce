<template>
  <div class="wysiwyg-editor border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-900 shadow-sm">
    <!-- Toolbar -->
    <div v-if="editor" class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 p-2 flex flex-wrap gap-1 rounded-t-lg">
      <!-- Text formatting -->
      <button
        type="button"
        class="inline-flex items-center justify-center w-8 h-8 rounded hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors"
        :class="{ 'bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400': editor.isActive('bold') }"
        @click="editor.chain().focus().toggleBold().run()"
        title="Жирний"
      >
        <Bold class="w-4 h-4" />
      </button>

      <button
        type="button"
        class="inline-flex items-center justify-center w-8 h-8 rounded hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors"
        :class="{ 'bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400': editor.isActive('italic') }"
        @click="editor.chain().focus().toggleItalic().run()"
        title="Курсив"
      >
        <Italic class="w-4 h-4" />
      </button>

      <button
        type="button"
        class="inline-flex items-center justify-center w-8 h-8 rounded hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors"
        :class="{ 'bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400': editor.isActive('strike') }"
        @click="editor.chain().focus().toggleStrike().run()"
        title="Закреслений"
      >
        <Strikethrough class="w-4 h-4" />
      </button>

      <div class="w-px h-6 bg-gray-300 dark:bg-gray-600 mx-1 self-center" />

      <!-- Headings -->
      <button
        type="button"
        class="inline-flex items-center justify-center w-8 h-8 rounded hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors"
        :class="{ 'bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400': editor.isActive('heading', { level: 1 }) }"
        @click="editor.chain().focus().toggleHeading({ level: 1 }).run()"
        title="Заголовок 1"
      >
        <Heading1 class="w-4 h-4" />
      </button>

      <button
        type="button"
        class="inline-flex items-center justify-center w-8 h-8 rounded hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors"
        :class="{ 'bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400': editor.isActive('heading', { level: 2 }) }"
        @click="editor.chain().focus().toggleHeading({ level: 2 }).run()"
        title="Заголовок 2"
      >
        <Heading2 class="w-4 h-4" />
      </button>

      <button
        type="button"
        class="inline-flex items-center justify-center w-8 h-8 rounded hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors"
        :class="{ 'bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400': editor.isActive('heading', { level: 3 }) }"
        @click="editor.chain().focus().toggleHeading({ level: 3 }).run()"
        title="Заголовок 3"
      >
        <Heading3 class="w-4 h-4" />
      </button>

      <div class="w-px h-6 bg-gray-300 dark:bg-gray-600 mx-1 self-center" />

      <!-- Lists -->
      <button
        type="button"
        class="inline-flex items-center justify-center w-8 h-8 rounded hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors"
        :class="{ 'bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400': editor.isActive('bulletList') }"
        @click="editor.chain().focus().toggleBulletList().run()"
        title="Маркований список"
      >
        <List class="w-4 h-4" />
      </button>

      <button
        type="button"
        class="inline-flex items-center justify-center w-8 h-8 rounded hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors"
        :class="{ 'bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400': editor.isActive('orderedList') }"
        @click="editor.chain().focus().toggleOrderedList().run()"
        title="Нумерований список"
      >
        <ListOrdered class="w-4 h-4" />
      </button>

      <div class="w-px h-6 bg-gray-300 dark:bg-gray-600 mx-1 self-center" />

      <!-- Alignment -->
      <button
        type="button"
        class="inline-flex items-center justify-center w-8 h-8 rounded hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors"
        :class="{ 'bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400': editor.isActive({ textAlign: 'left' }) }"
        @click="editor.chain().focus().setTextAlign('left').run()"
        title="Вліво"
      >
        <AlignLeft class="w-4 h-4" />
      </button>

      <button
        type="button"
        class="inline-flex items-center justify-center w-8 h-8 rounded hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors"
        :class="{ 'bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400': editor.isActive({ textAlign: 'center' }) }"
        @click="editor.chain().focus().setTextAlign('center').run()"
        title="По центру"
      >
        <AlignCenter class="w-4 h-4" />
      </button>

      <button
        type="button"
        class="inline-flex items-center justify-center w-8 h-8 rounded hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors"
        :class="{ 'bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400': editor.isActive({ textAlign: 'right' }) }"
        @click="editor.chain().focus().setTextAlign('right').run()"
        title="Вправо"
      >
        <AlignRight class="w-4 h-4" />
      </button>

      <div class="w-px h-6 bg-gray-300 dark:bg-gray-600 mx-1 self-center" />

      <!-- Link -->
      <button
        type="button"
        class="inline-flex items-center justify-center w-8 h-8 rounded hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors"
        :class="{ 'bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400': editor.isActive('link') }"
        @click="setLink"
        title="Посилання"
      >
        <LinkIcon class="w-4 h-4" />
      </button>

      <!-- Code block -->
      <button
        type="button"
        class="inline-flex items-center justify-center w-8 h-8 rounded hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors"
        :class="{ 'bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400': editor.isActive('codeBlock') }"
        @click="editor.chain().focus().toggleCodeBlock().run()"
        title="Код"
      >
        <Code class="w-4 h-4" />
      </button>

      <div class="w-px h-6 bg-gray-300 dark:bg-gray-600 mx-1 self-center" />

      <!-- Image -->
      <button
        type="button"
        class="inline-flex items-center justify-center w-8 h-8 rounded hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors"
        @click="addImage"
        title="Зображення"
      >
        <ImageIcon class="w-4 h-4" />
      </button>

      <div class="w-px h-6 bg-gray-300 dark:bg-gray-600 mx-1 self-center" />

      <!-- Clear formatting -->
      <button
        type="button"
        class="inline-flex items-center justify-center w-8 h-8 rounded hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors text-gray-600 dark:text-gray-400"
        @click="editor.chain().focus().unsetAllMarks().run()"
        title="Очистити форматування"
      >
        <RemoveFormatting class="w-4 h-4" />
      </button>
    </div>

    <!-- Editor Content -->
    <EditorContent :editor="editor" class="p-4 min-h-[200px] max-h-[400px] overflow-y-auto" />
  </div>

  <!-- File Picker Modal -->
  <VFilePickerModal
    v-model:is-open="isFilePickerOpen"
    file-type="image"
    @select="handleFileSelect"
  />
</template>

<script setup lang="ts">
import { useEditor, EditorContent } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';
import TextAlign from '@tiptap/extension-text-align';
import Link from '@tiptap/extension-link';
import Image from '@tiptap/extension-image';
import {
  Bold,
  Italic,
  Strikethrough,
  Heading1,
  Heading2,
  Heading3,
  List,
  ListOrdered,
  AlignLeft,
  AlignCenter,
  AlignRight,
  Link as LinkIcon,
  Code,
  RemoveFormatting,
  Image as ImageIcon,
} from 'lucide-vue-next';
import VFilePickerModal from '~/components/files/modals/VFilePickerModal.vue';
import type { IFile } from '~/models/files';

interface Props {
  modelValue: string | null;
}

const props = defineProps<Props>();
const emit = defineEmits<{
  'update:modelValue': [value: string];
}>();

const { getFileBlob } = useFiles();
const imageBlobs = ref<Map<number, string>>(new Map());
const isFilePickerOpen = ref(false);
const isInitialized = ref(false);

const editor = useEditor({
  content: '',
  extensions: [
    StarterKit,
    TextAlign.configure({
      types: ['heading', 'paragraph'],
    }),
    Link.configure({
      openOnClick: false,
      HTMLAttributes: {
        class: 'text-primary-600 underline cursor-pointer',
      },
    }),
    Image.configure({
      HTMLAttributes: {
        class: 'rounded-lg max-w-full h-auto',
      },
      allowBase64: true,
    }),
  ],
  onCreate: async ({ editor }) => {
    if (props.modelValue) {
      const processedHtml = await convertFileIdsToBlobUrls(props.modelValue);
      editor.commands.setContent(processedHtml, { emitUpdate: false });
    }
    isInitialized.value = true;
  },
  onUpdate: ({ editor }) => {
    const html = editor.getHTML();
    const processedHtml = convertBlobUrlsToFileIds(html);
    emit('update:modelValue', processedHtml);
  },
  editorProps: {
    attributes: {
      class: 'prose dark:prose-invert prose-sm focus:outline-none max-w-none',
    },
  },
});

// Convert blob URLs to file IDs for storage
const convertBlobUrlsToFileIds = (html: string): string => {
  const parser = new DOMParser();
  const doc = parser.parseFromString(html, 'text/html');
  const images = doc.querySelectorAll('img');

  images.forEach(img => {
    const fileId = img.getAttribute('data-file-id');
    const src = img.getAttribute('src');

    if (fileId) {
      img.setAttribute('src', `file:${fileId}`);
      img.removeAttribute('data-file-id');
    } else if (src && src.startsWith('blob:')) {
      // If there's a blob URL but no file-id, try to find it in our map
      const foundEntry = Array.from(imageBlobs.value.entries()).find(([_, blobUrl]) => blobUrl === src);
      if (foundEntry) {
        img.setAttribute('src', `file:${foundEntry[0]}`);
      }
    }
  });

  return doc.body.innerHTML;
};

// Convert file IDs to blob URLs for display
const convertFileIdsToBlobUrls = async (html: string): Promise<string> => {
  const parser = new DOMParser();
  const doc = parser.parseFromString(html, 'text/html');
  const images = doc.querySelectorAll('img');

  for (const img of Array.from(images)) {
    const src = img.getAttribute('src');

    // Handle file: protocol
    if (src && src.startsWith('file:')) {
      const fileId = parseInt(src.replace('file:', ''));

      // Check if we already have the blob URL
      let blobUrl = imageBlobs.value.get(fileId);

      if (!blobUrl) {
        try {
          const blob = await getFileBlob(fileId);
          blobUrl = URL.createObjectURL(blob);
          imageBlobs.value.set(fileId, blobUrl);
        } catch (error) {
          console.error('Failed to load image:', error);
          continue;
        }
      }

      img.setAttribute('src', blobUrl);
      img.setAttribute('data-file-id', String(fileId));
    }
    // Handle old blob: URLs from server (need to be converted)
    else if (src && src.startsWith('blob:')) {
      // Try to get file-id from data attribute if exists
      const dataFileId = img.getAttribute('data-file-id');
      if (dataFileId) {
        const fileId = parseInt(dataFileId);
        let blobUrl = imageBlobs.value.get(fileId);

        if (!blobUrl) {
          try {
            const blob = await getFileBlob(fileId);
            blobUrl = URL.createObjectURL(blob);
            imageBlobs.value.set(fileId, blobUrl);
          } catch (error) {
            console.error('Failed to load image:', error);
            continue;
          }
        }

        img.setAttribute('src', blobUrl);
      }
    }
  }

  return doc.body.innerHTML;
};

// Watch for external changes and convert file IDs to blob URLs (after initialization)
watch(() => props.modelValue, async (value) => {
  if (!value || !editor.value || !isInitialized.value) return;

  const processedHtml = await convertFileIdsToBlobUrls(value);
  const isSame = editor.value?.getHTML() === processedHtml;

  if (!isSame) {
    editor.value?.commands.setContent(processedHtml, { emitUpdate: false });
  }
});

const setLink = () => {
  const previousUrl = editor.value?.getAttributes('link').href;
  const url = window.prompt('URL:', previousUrl);

  if (url === null) {
    return;
  }

  if (url === '') {
    editor.value?.chain().focus().extendMarkRange('link').unsetLink().run();
    return;
  }

  editor.value?.chain().focus().extendMarkRange('link').setLink({ href: url }).run();
};

const addImage = () => {
  isFilePickerOpen.value = true;
};

const handleFileSelect = async (files: IFile[]) => {
  if (files.length === 0) return;

  try {
    // Process all selected files
    for (const selectedFile of files) {
      // Get the file blob and create object URL
      const blob = await getFileBlob(selectedFile.id);
      const imageUrl = URL.createObjectURL(blob);

      // Store the blob URL for cleanup
      imageBlobs.value.set(selectedFile.id, imageUrl);

      // Insert the image into the editor
      editor.value?.chain().focus().setImage({ src: imageUrl }).run();

      // Add data-file-id attribute to the inserted image
      // Use setTimeout to ensure DOM is updated
      await new Promise(resolve => setTimeout(resolve, 50));
      const editorElement = editor.value?.view.dom;
      if (editorElement) {
        const images = editorElement.querySelectorAll('img');
        // Find the image with our URL
        Array.from(images).forEach(img => {
          if (img.getAttribute('src') === imageUrl && !img.getAttribute('data-file-id')) {
            img.setAttribute('data-file-id', String(selectedFile.id));
          }
        });
      }
    }
  } catch (error) {
    console.error('Failed to load image:', error);
  }

  isFilePickerOpen.value = false;
};

onBeforeUnmount(() => {
  // Cleanup blob URLs
  imageBlobs.value.forEach(url => URL.revokeObjectURL(url));
  imageBlobs.value.clear();

  editor.value?.destroy();
});
</script>

<style scoped>
.wysiwyg-editor :deep(.ProseMirror) {
  outline: none;
}

.wysiwyg-editor :deep(.ProseMirror p.is-editor-empty:first-child::before) {
  color: #adb5bd;
  content: attr(data-placeholder);
  float: left;
  height: 0;
  pointer-events: none;
}

.wysiwyg-editor :deep(.ProseMirror h1) {
  font-size: 2em;
  font-weight: bold;
  margin: 0.67em 0;
}

.wysiwyg-editor :deep(.ProseMirror h2) {
  font-size: 1.5em;
  font-weight: bold;
  margin: 0.75em 0;
}

.wysiwyg-editor :deep(.ProseMirror h3) {
  font-size: 1.17em;
  font-weight: bold;
  margin: 0.83em 0;
}

.wysiwyg-editor :deep(.ProseMirror ul),
.wysiwyg-editor :deep(.ProseMirror ol) {
  padding-left: 1.5em;
  margin: 1em 0;
}

.wysiwyg-editor :deep(.ProseMirror code) {
  background-color: rgba(97, 97, 97, 0.1);
  border-radius: 0.25em;
  padding: 0.2em 0.4em;
  font-family: monospace;
}

.wysiwyg-editor :deep(.ProseMirror pre) {
  background-color: rgba(97, 97, 97, 0.1);
  border-radius: 0.5em;
  padding: 0.75em 1em;
  font-family: monospace;
  overflow-x: auto;
}

.wysiwyg-editor :deep(.ProseMirror blockquote) {
  border-left: 3px solid rgba(97, 97, 97, 0.2);
  padding-left: 1em;
  margin-left: 0;
}

.wysiwyg-editor :deep(.ProseMirror img) {
  max-width: 100%;
  height: auto;
  border-radius: 0.5em;
  margin: 1em 0;
}

.wysiwyg-editor :deep(.ProseMirror img.ProseMirror-selectednode) {
  outline: 3px solid var(--primary-500, #3b82f6);
  outline-offset: 2px;
}
</style>
