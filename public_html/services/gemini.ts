
import { GoogleGenAI, GenerateContentResponse } from "@google/genai";

// Always use the API key from environment variables directly.
const ai = new GoogleGenAI({ apiKey: process.env.API_KEY });

export const GeminiService = {
  /**
   * Summarizes customer reviews using Gemini.
   */
  summarizeReviews: async (reviews: string[]) => {
    try {
      const response: GenerateContentResponse = await ai.models.generateContent({
        model: 'gemini-3-flash-preview',
        contents: `Please summarize these customer reviews for our coffee brand into a short, constructive paragraph for the owner. Identify themes like quality, delivery, and taste.\n\nReviews: ${reviews.join('\n')}`,
      });
      // Extract generated text directly from the .text property.
      return response.text;
    } catch (e) {
      console.error(e);
      return "Could not generate summary.";
    }
  },
  /**
   * Drafts a customer service reply using Gemini.
   */
  draftReply: async (message: string) => {
    try {
      const response: GenerateContentResponse = await ai.models.generateContent({
        model: 'gemini-3-flash-preview',
        contents: `Draft a professional, warm customer service reply to this message from a coffee customer. Brand name is Falls Origin Coffee. The reply should be polite and helpful.\n\nMessage: ${message}`,
      });
      // Extract generated text directly from the .text property.
      return response.text;
    } catch (e) {
      console.error(e);
      return "Could not draft reply.";
    }
  }
};
