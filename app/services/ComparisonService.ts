import type { Product } from "~/models/product";

export interface ComparisonListItem {
  category: {
    id: number;
    name: string;
    slug: string;
    logo_file_id?: number | null;
  };
  products_count: number;
  product_ids: number[];
  preview_images: number[];
}

export interface ComparisonTableRow {
  label: string;
  type: "specification" | "attribute" | "basic";
  values: (string | null)[];
  isDifferent: boolean;
}

export interface ComparisonData {
  category: {
    id: number;
    name: string;
    slug: string;
  };
  products: Product[];
  table: ComparisonTableRow[];
}

interface ComparisonByIdsResponse {
  success: boolean;
  data: Array<{
    category: { id: number; name: string; slug: string } | null;
    products: Product[];
  }>;
}

export class ComparisonService {
  /**
   * Get comparison products by IDs (public endpoint for guests/shared links).
   */
  static async getProductsByIds(productIds: number[]): Promise<Product[]> {
    const client = useSanctumClient();

    try {
      const response = await client<ComparisonByIdsResponse>(
        "/api/comparisons/by-ids",
        {
          method: "POST",
          body: { product_ids: productIds },
        }
      );

      // Flatten all products
      return response.data.flatMap((group) => group.products);
    } catch (error) {
      console.error("Failed to get products by IDs:", error);
      return [];
    }
  }

  /**
   * Build comparison table data from products.
   */
  static buildComparisonTable(products: Product[]): ComparisonTableRow[] {
    const rows: ComparisonTableRow[] = [];

    if (products.length === 0) return rows;

    // Brand row
    rows.push({
      label: "Бренд",
      type: "basic",
      values: products.map((p) => p.brand?.name ?? null),
      isDifferent: false,
    });

    // Price row
    rows.push({
      label: "Ціна",
      type: "basic",
      values: products.map((p) => {
        const price = p.current_price || p.base_price;
        return `${Number(price).toLocaleString("uk-UA")} грн`;
      }),
      isDifferent: false,
    });

    // Collect all unique specification names
    const specNames = new Set<string>();
    for (const product of products) {
      if (product.specifications) {
        for (const spec of product.specifications) {
          specNames.add(spec.name);
        }
      }
    }

    // Add specification rows
    for (const specName of specNames) {
      const values = products.map((product) => {
        const spec = product.specifications?.find((s) => s.name === specName);
        return spec?.value ?? null;
      });

      rows.push({
        label: specName,
        type: "specification",
        values,
        isDifferent: false,
      });
    }

    // Calculate isDifferent for each row
    for (const row of rows) {
      const nonNullValues = row.values.filter((v) => v !== null);
      const uniqueValues = new Set(nonNullValues);
      row.isDifferent = uniqueValues.size > 1;
    }

    return rows;
  }

  /**
   * Filter table to show only differences.
   */
  static filterDifferencesOnly(
    rows: ComparisonTableRow[]
  ): ComparisonTableRow[] {
    return rows.filter((row) => row.isDifferent);
  }

  /**
   * Generate share URL with product IDs.
   */
  static generateShareUrl(categorySlug: string, productIds: number[]): string {
    const params = new URLSearchParams();
    params.set("ids", productIds.join(","));
    return `/comparison/${categorySlug}?${params.toString()}`;
  }

  /**
   * Parse product IDs from share URL.
   */
  static parseShareUrl(url: string): number[] {
    const params = new URLSearchParams(url.split("?")[1] || "");
    const ids = params.get("ids");
    if (!ids) return [];
    return ids
      .split(",")
      .map((id) => parseInt(id, 10))
      .filter((id) => !isNaN(id));
  }
}
