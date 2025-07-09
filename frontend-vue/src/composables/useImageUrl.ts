interface Size {
    type: string;
    url: string;
}

export const useImageUrl = () => {
  const getAdjustedQualityImageUrl = (sizes: Size[], currentColumnClass: string): string => {
    const sizeMapping: Record<string, string[]> = {
      'col-6': ['w', 'z', 'x', 'm'],
      'col-4': ['z', 'x', 'm', 'w'],
      'col-3': ['x', 'm', 'z', 'w']
    }

    const preferredSizes = sizeMapping[currentColumnClass] || ['m', 'x', 'z', 'w']

    const foundSizeType = preferredSizes.find(sizeType => sizes.some(size => size.type === sizeType))

    return foundSizeType ? sizes.find(size => size.type === foundSizeType)?.url || '' : ''
  }

  return {
    getAdjustedQualityImageUrl
  }
}
