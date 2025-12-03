<template>
  <div class="w-full">
    <Transition name="fade">
      <div
        v-if="onPage && showContent"
        class="fixed inset-0 bg-black/50 z-40"
        @mouseenter="handleLeave"
      />
    </Transition>

    <div
      class="flex items-start bg-white w-full"
      :class="{ 'relative z-50 overlay': onPage && showContent }"
    >
      <div ref="categoriesListRef" class="max-w-[340px] w-full shrink-0">
        <div
          v-for="index in 10"
          :key="index"
          class="flex items-center gap-3 p-3 rounded-[14px] cursor-pointer hover:bg-primary-50 duration-200"
          :class="{
            'bg-primary-50':
              activeCategory === index || (!isInteractive && index === 2),
          }"
          @mouseenter="handleCategoryHover(index)"
        >
          <img
            src="https://telemart.ua/theme/main/i/c/c1-h.svg"
            alt="category"
          />
          <span>Категорія номер {{ index }}</span>
          <ChevronRight class="ml-auto" />
        </div>
      </div>

      <div
        v-if="!isInteractive || showContent"
        class="flex-1 overflow-auto pl-12 bg-white"
        :style="{ maxHeight: `${listHeight}px` }"
      >
        Lorem ipsum, dolor sit amet consectetur adipisicing elit. Magnam
        possimus nulla ab accusantium dignissimos quidem? Quos totam iure
        quaerat eius atque incidunt eaque necessitatibus nesciunt, natus
        tempora, illo quae dignissimos rem ea voluptatem nobis inventore! Velit
        ipsum reiciendis ut repudiandae ab magni, possimus quis amet ipsam, cum
        enim tempora aperiam non quaerat fugit maiores doloremque minima nobis.
        Dignissimos aspernatur corrupti placeat sint accusantium facere! Aut
        vero ipsam, atque eius consequatur voluptate facilis iure, quibusdam
        odit praesentium nobis saepe magnam id harum neque dolor expedita
        perspiciatis quis, eos autem architecto tenetur modi. Numquam vel
        dolores porro veritatis non repellendus necessitatibus, aliquam
        blanditiis quae earum, dolor impedit labore autem esse est, possimus
        odio sint magni at delectus aut iste ea expedita quis. Minima quam ipsa
        veniam incidunt dolores laborum, illo provident labore, assumenda
        quisquam quo? Laborum ut dolorum temporibus modi consequatur, nisi
        quibusdam consequuntur iusto, ipsum voluptatibus expedita aspernatur
        magnam, aut quasi natus accusamus officiis? Reiciendis aspernatur iure
        quos sed deleniti debitis, error provident nemo, eligendi dolor qui rem
        exercitationem eaque magnam unde commodi quaerat natus ad dolorum
        dolorem praesentium laborum culpa? At similique excepturi corrupti culpa
        ex libero expedita nesciunt, praesentium voluptatem quasi architecto
        quidem quas sit, blanditiis non dolore. Totam voluptatibus eos a nisi
        est voluptate sequi fugit reprehenderit esse dignissimos fuga ullam,
        minima, itaque enim, magni ad vero quos tempore error delectus! Iusto
        quam consequatur aperiam omnis optio nostrum harum reiciendis fugit
        adipisci voluptatibus quisquam nihil facere, iure et placeat voluptas
        quasi eum officiis, quo consectetur! Dolor aliquam, consequuntur, est
        debitis error nihil natus voluptate ut distinctio vitae incidunt dolore,
        eligendi dolores? Accusantium ipsam accusamus facere nulla et quam quis
        obcaecati ipsum non qui ullam numquam, fugiat impedit fugit deserunt
        aspernatur repudiandae tenetur, aperiam facilis, corrupti eum. Quidem
        deleniti illum, voluptatibus consequuntur eaque possimus, eum alias quae
        autem distinctio, aliquam sit rem aperiam? Explicabo, rerum facere
        soluta dignissimos voluptatum ea reprehenderit dolore nesciunt sit non
        tempora voluptatem deserunt placeat doloribus fuga, delectus labore quae
        sed vel dolor tempore dolores? Deleniti, dignissimos autem, velit
        dolores nobis iusto veniam odit corrupti soluta beatae ipsa, commodi
        nemo iste dolore vero nesciunt officiis sapiente reiciendis accusantium
        assumenda minima. Laudantium quia nihil explicabo placeat neque
        architecto, mollitia libero, optio perferendis quo quaerat! Amet magni
        dolore ipsa architecto quia, doloremque et fuga ea laudantium quam
        nostrum maiores totam saepe iste commodi unde quas optio cupiditate
        quibusdam voluptatem fugit eum similique! Ullam expedita facere, aliquam
        dicta numquam libero accusamus commodi distinctio odio reiciendis earum
        enim, quas repellat rerum hic eius laboriosam delectus iste doloremque
        quam. Non omnis distinctio consequuntur. Dolorem beatae ut dolore rerum
        quos a? Perspiciatis harum veniam id quam, aperiam non cumque quibusdam
        labore quas? Harum, velit. Dolor fugiat corporis hic, itaque tempora,
        sed dolorem repellendus laborum consequuntur quam quasi maxime optio
        porro. Quasi doloremque, vel obcaecati laudantium dicta in repellendus.
        Tempore obcaecati soluta amet, voluptatum voluptate earum accusamus
        temporibus ut facere? Explicabo temporibus consequuntur distinctio eaque
        velit. Aliquam aspernatur quia voluptates maxime vel exercitationem eum
        eius, fugiat animi nemo hic veritatis officiis ipsam accusamus eaque
        odio nobis. Earum, dolorem sint. Maiores aspernatur ipsa labore ut
        obcaecati nemo, corporis ex ea facere aut! Qui facilis deleniti
        doloremque fugiat nam consequuntur blanditiis ipsum, soluta repudiandae,
        quisquam at beatae fugit magni provident aut dolores incidunt eum? Eaque
        laboriosam magnam expedita nihil neque corporis hic aspernatur animi
        assumenda nesciunt cupiditate repudiandae, dolorem eveniet temporibus
        voluptatibus natus amet quisquam, quod reprehenderit vero molestiae a
        quasi ad. Odio, omnis consequatur vero impedit quos id aliquam quisquam
        ex! Maiores hic in cum, molestiae iusto dolores voluptas illo fugiat
        nesciunt, nam assumenda quas veritatis excepturi suscipit debitis sunt,
        fugit cumque expedita?
        <UCarousel />
      </div>

      <div v-if="onPage && !showContent" class="flex-1 w-full">
        <slot />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ChevronRight } from "lucide-vue-next";

interface IProps {
  onPage?: boolean;
  inModal?: boolean;
}

const { onPage, inModal } = defineProps<IProps>();

const isInteractive = computed(() => onPage || inModal);

const categoriesListRef = ref<HTMLElement | null>(null);
const listHeight = ref(0);
const activeCategory = ref<number | null>(null);
const showContent = ref(false);

const handleCategoryHover = (index: number) => {
  if (isInteractive.value) {
    activeCategory.value = index;
    showContent.value = true;
  }
};

const handleLeave = () => {
  showContent.value = false;
};

onMounted(() => {
  nextTick(() => {
    if (categoriesListRef.value) {
      listHeight.value = categoriesListRef.value.scrollHeight;
    }

    if (inModal) {
      activeCategory.value = 1;
      showContent.value = true;
    }
  });
});
</script>

<style scoped lang="scss">
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.overlay {
  &::before {
    content: "";
    position: absolute;
    top: -0.75rem;
    left: -0.75rem;
    right: -0.75rem;
    bottom: -0.75rem;
    z-index: -1;
    width: calc(100% + 1.5rem);
    height: calc(100% + 1.5rem);
    border-radius: 1rem;
    background-color: rgba(255, 255, 255);
  }
}
</style>
